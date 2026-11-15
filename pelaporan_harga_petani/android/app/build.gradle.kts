import java.util.Properties
import org.gradle.api.GradleException

plugins {
    id("com.android.application")
    id("kotlin-android")
    // The Flutter Gradle Plugin must be applied after the Android and Kotlin Gradle plugins.
    id("dev.flutter.flutter-gradle-plugin")
}

val keystoreProperties = Properties()
val keystorePropertiesFile = rootProject.file("key.properties")
if (keystorePropertiesFile.exists()) {
    keystorePropertiesFile.inputStream().use { keystoreProperties.load(it) }
}
val hasReleaseSigning =
    keystorePropertiesFile.exists() &&
        !keystoreProperties.getProperty("storeFile").isNullOrBlank() &&
        !keystoreProperties.getProperty("storePassword").isNullOrBlank() &&
        !keystoreProperties.getProperty("keyAlias").isNullOrBlank() &&
        !keystoreProperties.getProperty("keyPassword").isNullOrBlank()
val allowDebugRelease = project.hasProperty("allowDebugRelease") &&
    project.property("allowDebugRelease").toString().toBoolean()

android {
    namespace = "com.example.pelaporan_harga_petani"
    compileSdk = flutter.compileSdkVersion
    ndkVersion = flutter.ndkVersion

    compileOptions {
        sourceCompatibility = JavaVersion.VERSION_17
        targetCompatibility = JavaVersion.VERSION_17
    }

    kotlinOptions {
        jvmTarget = JavaVersion.VERSION_17.toString()
    }

    defaultConfig {
        applicationId = "id.petani.pelaporanhargapanen"
        minSdk = flutter.minSdkVersion
        targetSdk = flutter.targetSdkVersion
        versionCode = flutter.versionCode
        versionName = flutter.versionName
    }

    signingConfigs {
        if (hasReleaseSigning) {
            create("release") {
                keyAlias = keystoreProperties.getProperty("keyAlias")
                keyPassword = keystoreProperties.getProperty("keyPassword")
                storeFile = rootProject.file(keystoreProperties.getProperty("storeFile"))
                storePassword = keystoreProperties.getProperty("storePassword")
            }
        }
    }

    buildTypes {
        release {
            signingConfig = when {
                hasReleaseSigning -> signingConfigs.getByName("release")
                allowDebugRelease -> signingConfigs.getByName("debug")
                else -> signingConfigs.getByName("debug")
            }
        }
    }
}

gradle.taskGraph.whenReady {
    val runningReleaseTask = allTasks.any { task ->
        task.name.contains("Release", ignoreCase = false)
    }
    if (runningReleaseTask && !hasReleaseSigning && !allowDebugRelease) {
        throw GradleException(
            "android/key.properties belum dikonfigurasi untuk release build. " +
                "Salin android/key.properties.example ke android/key.properties, " +
                "atau jalankan dengan -PallowDebugRelease=true khusus pengujian lokal."
        )
    }
}

flutter {
    source = "../.."
}
