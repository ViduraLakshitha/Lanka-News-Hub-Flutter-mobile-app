class UserModel {
  final int id;
  final String name;
  final String email;
  final String role;
  final String status;
  final List<String> allowedLanguages;
  final String? phone;
  final String? token;

  UserModel({
    required this.id,
    required this.name,
    required this.email,
    required this.role,
    this.status = 'active',
    this.allowedLanguages = const ['en', 'si', 'ta'],
    this.phone,
    this.token,
  });

  bool get isAdmin => role == 'super_admin' || role == 'editor';

  factory UserModel.fromMap(Map<String, dynamic> map, {String? token}) {
    final langList = <String>[];
    if (map['allowed_languages'] is List) {
      for (final l in map['allowed_languages'] as List) {
        langList.add(l.toString());
      }
    } else if (map['allowed_languages'] is String) {
      langList.addAll((map['allowed_languages'] as String).split(',').map((e) => e.trim()));
    }

    return UserModel(
      id: map['id'] is int ? map['id'] : int.tryParse(map['id'].toString()) ?? 0,
      name: map['name']?.toString() ?? '',
      email: map['email']?.toString() ?? '',
      role: map['role']?.toString() ?? 'member',
      status: map['status']?.toString() ?? 'active',
      allowedLanguages: langList.isNotEmpty ? langList : ['en', 'si', 'ta'],
      phone: map['phone']?.toString(),
      token: token ?? map['token']?.toString(),
    );
  }

  Map<String, dynamic> toMap() {
    return {
      'id': id,
      'name': name,
      'email': email,
      'role': role,
      'status': status,
      'allowed_languages': allowedLanguages,
      'phone': phone,
      'token': token,
    };
  }
}

