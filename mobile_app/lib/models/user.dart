import 'package:equatable/equatable.dart';

/// User model representing an authenticated employee
class User extends Equatable {
  final int id;
  final String username;
  final String firstName;
  final String lastName;
  final String branchCode;
  final String branchName;
  final String token;
  final DateTime expiresAt;

  const User({
    required this.id,
    required this.username,
    required this.firstName,
    required this.lastName,
    required this.branchCode,
    required this.branchName,
    required this.token,
    required this.expiresAt,
  });

  String get fullName => '$firstName $lastName';

  bool get isTokenExpired => DateTime.now().isAfter(expiresAt);

  factory User.fromJson(Map<String, dynamic> json) {
    final employee = json['employee'] as Map<String, dynamic>;
    final branch = json['branch'] as Map<String, dynamic>;

    return User(
      id: int.parse(employee['id'].toString()),
      username: employee['username'] as String,
      firstName: employee['first_name'] as String,
      lastName: employee['last_name'] as String,
      branchCode: branch['code'] as String,
      branchName: branch['name'] as String,
      token: json['token'] as String,
      expiresAt: DateTime.parse(json['expires_at'] as String),
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'employee': {
        'id': id.toString(),
        'username': username,
        'first_name': firstName,
        'last_name': lastName,
      },
      'branch': {
        'code': branchCode,
        'name': branchName,
      },
      'token': token,
      'expires_at': expiresAt.toIso8601String(),
    };
  }

  @override
  List<Object?> get props => [id, username, token];
}
