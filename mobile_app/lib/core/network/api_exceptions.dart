class ApiException implements Exception {
  final String message;
  final int? statusCode;
  final dynamic errors;

  ApiException(this.message, {this.statusCode, this.errors});

  @override
  String toString() => message;
}

class NetworkException extends ApiException {
  NetworkException([super.message = 'Cannot connect to server. Please check your internet.'])
      : super(statusCode: 0);
}

class UnauthorizedException extends ApiException {
  UnauthorizedException([super.message = 'Session expired. Please sign in again.'])
      : super(statusCode: 401);
}
