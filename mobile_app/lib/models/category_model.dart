class CategoryModel {
  final int id;
  final String slug;
  final String name;
  final String nameEn;
  final String nameSi;
  final String nameTa;
  final String color;
  final String icon;
  final int order;

  CategoryModel({
    required this.id,
    required this.slug,
    required this.name,
    required this.nameEn,
    required this.nameSi,
    required this.nameTa,
    this.color = '#C41230',
    this.icon = 'newspaper',
    this.order = 0,
  });

  String getLocalizedName(String langCode) {
    if (langCode == 'si' && nameSi.isNotEmpty) return nameSi;
    if (langCode == 'ta' && nameTa.isNotEmpty) return nameTa;
    return nameEn.isNotEmpty ? nameEn : name;
  }

  /// Maps category slugs or names to their database category ID where articles live.
  static String resolveQueryParam(String? slugOrId, [int? id]) {
    if (id != null && id > 0 && id <= 7) {
      return id.toString();
    }
    if (slugOrId == null || slugOrId.isEmpty) return '';
    final s = slugOrId.toLowerCase().trim();
    return switch (s) {
      'local' || 'sri-lanka' || '1' => '1',
      'politics' || '2' => '2',
      'business' || '3' => '3',
      'sports' || '4' => '4',
      'entertainment' || 'world' || '5' => '5',
      'technology' || 'tech' || '6' => '6',
      'international' || '7' => '7',
      _ => slugOrId,
    };
  }

  factory CategoryModel.fromMap(Map<String, dynamic> map) {
    return CategoryModel(
      id: map['id'] is int ? map['id'] : int.tryParse(map['id'].toString()) ?? 0,
      slug: map['slug']?.toString() ?? '',
      name: map['name']?.toString() ?? '',
      nameEn: map['name_en']?.toString() ?? '',
      nameSi: map['name_si']?.toString() ?? '',
      nameTa: map['name_ta']?.toString() ?? '',
      color: map['color']?.toString() ?? '#C41230',
      icon: map['icon']?.toString() ?? 'newspaper',
      order: map['order'] is int ? map['order'] : int.tryParse(map['order']?.toString() ?? '0') ?? 0,
    );
  }

  Map<String, dynamic> toMap() {
    return {
      'id': id,
      'slug': slug,
      'name': name,
      'name_en': nameEn,
      'name_si': nameSi,
      'name_ta': nameTa,
      'color': color,
      'icon': icon,
      'order': order,
    };
  }
}

