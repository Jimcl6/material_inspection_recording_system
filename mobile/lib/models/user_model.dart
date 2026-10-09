class UserRole {
  final int id;
  final String name;
  final String slug;

  UserRole({required this.id, required this.name, required this.slug});

  factory UserRole.fromJson(Map<String, dynamic> json) {
    return UserRole(
      id: json['id'] as int? ?? 0,
      name: json['name'] as String? ?? '',
      slug: json['slug'] as String? ?? '',
    );
  }

  Map<String, dynamic> toJson() => {'id': id, 'name': name, 'slug': slug};
}

class DepartmentModel {
  final int id;
  final String name;
  final String? code;

  DepartmentModel({required this.id, required this.name, this.code});

  factory DepartmentModel.fromJson(Map<String, dynamic> json) {
    return DepartmentModel(
      id: json['id'] as int? ?? 0,
      name: json['name'] as String? ?? '',
      code: json['code'] as String?,
    );
  }

  Map<String, dynamic> toJson() => {'id': id, 'name': name, 'code': code};
}

class PositionModel {
  final int id;
  final String name;
  final String? code;

  PositionModel({required this.id, required this.name, this.code});

  factory PositionModel.fromJson(Map<String, dynamic> json) {
    return PositionModel(
      id: json['id'] as int? ?? 0,
      name: json['name'] as String? ?? '',
      code: json['code'] as String?,
    );
  }

  Map<String, dynamic> toJson() => {'id': id, 'name': name, 'code': code};
}

class UserModel {
  final int id;
  final String name;
  final String email;
  final String? employeeId;
  final UserRole? role;
  final DepartmentModel? department;
  final PositionModel? position;
  final bool isAdmin;
  final bool isSuperAdmin;
  final Map<String, Map<String, bool>> permissions;

  UserModel({
    required this.id,
    required this.name,
    required this.email,
    this.employeeId,
    this.role,
    this.department,
    this.position,
    this.isAdmin = false,
    this.isSuperAdmin = false,
    this.permissions = const {},
  });

  factory UserModel.fromJson(
    Map<String, dynamic> json, [
    Map<String, dynamic>? permissionsJson,
  ]) {
    final perms = <String, Map<String, bool>>{};
    if (permissionsJson != null) {
      permissionsJson.forEach((module, actions) {
        if (actions is Map) {
          perms[module] = {};
          actions.forEach((act, allowed) {
            perms[module]![act.toString()] = allowed == true;
          });
        }
      });
    }

    return UserModel(
      id: json['id'] as int? ?? 0,
      name: json['name'] as String? ?? '',
      email: json['email'] as String? ?? '',
      employeeId: json['employee_id'] as String?,
      role: json['role'] != null ? UserRole.fromJson(json['role'] as Map<String, dynamic>) : null,
      department: json['department'] != null ? DepartmentModel.fromJson(json['department'] as Map<String, dynamic>) : null,
      position: json['position'] != null ? PositionModel.fromJson(json['position'] as Map<String, dynamic>) : null,
      isAdmin: json['is_admin'] == true,
      isSuperAdmin: json['is_super_admin'] == true,
      permissions: perms,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'name': name,
      'email': email,
      'employee_id': employeeId,
      'role': role?.toJson(),
      'department': department?.toJson(),
      'position': position?.toJson(),
      'is_admin': isAdmin,
      'is_super_admin': isSuperAdmin,
      'permissions': permissions,
    };
  }

  bool can(String module, String action) {
    if (isSuperAdmin) return true;
    return permissions[module]?[action] ?? false;
  }
}
