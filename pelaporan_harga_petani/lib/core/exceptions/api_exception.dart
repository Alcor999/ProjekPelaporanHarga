class ApiException implements Exception {
  final String message;
  final int? statusCode;
  final dynamic originalError;

  ApiException({
    required this.message,
    this.statusCode,
    this.originalError,
  });

  @override
  String toString() => message;
}

class NetworkException extends ApiException {
  NetworkException({required String message})
      : super(message: message, statusCode: 0);
}

class UnauthorizedException extends ApiException {
  UnauthorizedException({required String message})
      : super(message: message, statusCode: 401);
}

class ForbiddenException extends ApiException {
  ForbiddenException({required String message})
      : super(message: message, statusCode: 403);
}

class NotFoundException extends ApiException {
  NotFoundException({required String message})
      : super(message: message, statusCode: 404);
}

class ValidationException extends ApiException {
  final Map<String, List<String>> errors;

  ValidationException({
    required String message,
    required this.errors,
  }) : super(message: message, statusCode: 422);
}

class ServerException extends ApiException {
  ServerException({required String message})
      : super(message: message, statusCode: 500);
}

