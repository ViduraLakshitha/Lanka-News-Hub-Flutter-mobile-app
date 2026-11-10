import 'dart:io';
import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:provider/provider.dart';
import '../../providers/classified_provider.dart';
import '../../providers/auth_provider.dart';
import '../../core/constants/app_colors.dart';
import '../../core/localization/app_localizations.dart';

class PostAdScreen extends StatefulWidget {
  const PostAdScreen({super.key});

  @override
  State<PostAdScreen> createState() => _PostAdScreenState();
}

class _PostAdScreenState extends State<PostAdScreen> {
  final _formKey = GlobalKey<FormState>();

  int? _selectedCategoryId;
  String _language = 'en';
  final _titleController = TextEditingController();
  final _descController = TextEditingController();
  final _locationController = TextEditingController();
  final _priceController = TextEditingController();
  final _nameController = TextEditingController();
  final _phoneController = TextEditingController();
  final _emailController = TextEditingController();
  bool _premiumRequested = false;

  final List<File> _selectedImages = [];
  final ImagePicker _picker = ImagePicker();
  bool _isSubmitting = false;

  @override
  void initState() {
    super.initState();
    final auth = Provider.of<AuthProvider>(context, listen: false);
    if (auth.user != null) {
      _nameController.text = auth.user!.name;
      _emailController.text = auth.user!.email;
      if (auth.user!.phone != null) {
        _phoneController.text = auth.user!.phone!;
      }
    }
  }

  Future<void> _pickImage(ImageSource source) async {
    if (_selectedImages.length >= 8) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Maximum 8 images allowed')),
      );
      return;
    }

    try {
      if (source == ImageSource.gallery) {
        final List<XFile> picked = await _picker.pickMultiImage();
        for (final x in picked) {
          if (_selectedImages.length < 8) {
            _selectedImages.add(File(x.path));
          }
        }
      } else {
        final XFile? photo = await _picker.pickImage(source: ImageSource.camera);
        if (photo != null && _selectedImages.length < 8) {
          _selectedImages.add(File(photo.path));
        }
      }
      setState(() {});
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('Image picker error: $e')),
        );
      }
    }
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;
    if (_selectedCategoryId == null) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('Please select a category')),
      );
      return;
    }

    setState(() => _isSubmitting = true);

    final provider = Provider.of<ClassifiedProvider>(context, listen: false);
    final success = await provider.submitAdvertisement(
      categoryId: _selectedCategoryId!,
      language: _language,
      title: _titleController.text.trim(),
      description: _descController.text.trim(),
      location: _locationController.text.trim(),
      price: _priceController.text.trim().isNotEmpty ? double.tryParse(_priceController.text.trim()) : null,
      contactName: _nameController.text.trim(),
      contactEmail: _emailController.text.trim(),
      contactPhone: _phoneController.text.trim(),
      premiumRequested: _premiumRequested,
      imageFiles: _selectedImages,
    );

    setState(() => _isSubmitting = false);

    if (mounted) {
      if (success) {
        showDialog(
          context: context,
          barrierDismissible: false,
          builder: (ctx) => AlertDialog(
            title: const Row(
              children: [
                Icon(Icons.check_circle, color: Colors.green),
                SizedBox(width: 8),
                Text('Ad Submitted'),
              ],
            ),
            content: const Text(
              'Your advertisement has been submitted successfully for editorial moderation. Once approved, it will be visible to thousands of buyers across Sri Lanka.',
            ),
            actions: [
              ElevatedButton(
                onPressed: () {
                  Navigator.pop(ctx);
                  Navigator.pop(context);
                },
                child: const Text('OK'),
              ),
            ],
          ),
        );
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(provider.errorMessage ?? 'Submission failed')),
        );
      }
    }
  }

  @override
  void dispose() {
    _titleController.dispose();
    _descController.dispose();
    _locationController.dispose();
    _priceController.dispose();
    _nameController.dispose();
    _phoneController.dispose();
    _emailController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    final categories = context.watch<ClassifiedProvider>().categories;

    return Scaffold(
      appBar: AppBar(
        title: Text(context.tr('post_ad')),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Photos section
              Text(
                context.tr('ad_images'),
                style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
              ),
              const SizedBox(height: 8),

              SizedBox(
                height: 90,
                child: ListView(
                  scrollDirection: Axis.horizontal,
                  children: [
                    // Add Photo Box
                    InkWell(
                      onTap: () {
                        showModalBottomSheet(
                          context: context,
                          builder: (_) => SafeArea(
                            child: Wrap(
                              children: [
                                ListTile(
                                  leading: const Icon(Icons.photo_library),
                                  title: const Text('Choose from Gallery'),
                                  onTap: () {
                                    Navigator.pop(context);
                                    _pickImage(ImageSource.gallery);
                                  },
                                ),
                                ListTile(
                                  leading: const Icon(Icons.camera_alt),
                                  title: const Text('Take a Photo'),
                                  onTap: () {
                                    Navigator.pop(context);
                                    _pickImage(ImageSource.camera);
                                  },
                                ),
                              ],
                            ),
                          ),
                        );
                      },
                      child: Container(
                        width: 90,
                        height: 90,
                        margin: const EdgeInsets.only(right: 8),
                        decoration: BoxDecoration(
                          color: Theme.of(context).cardTheme.color,
                          borderRadius: BorderRadius.circular(10),
                          border: Border.all(color: AppColors.primary, width: 1.5),
                        ),
                        child: const Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Icon(Icons.add_a_photo, color: AppColors.primary),
                            SizedBox(height: 4),
                            Text('Add', style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold)),
                          ],
                        ),
                      ),
                    ),

                    // Thumbnails list
                    ..._selectedImages.asMap().entries.map((entry) {
                      final idx = entry.key;
                      final file = entry.value;
                      return Stack(
                        children: [
                          Container(
                            width: 90,
                            height: 90,
                            margin: const EdgeInsets.only(right: 8),
                            decoration: BoxDecoration(
                              borderRadius: BorderRadius.circular(10),
                              image: DecorationImage(image: FileImage(file), fit: BoxFit.cover),
                            ),
                          ),
                          Positioned(
                            top: 4,
                            right: 12,
                            child: InkWell(
                              onTap: () => setState(() => _selectedImages.removeAt(idx)),
                              child: const CircleAvatar(
                                radius: 11,
                                backgroundColor: Colors.red,
                                child: Icon(Icons.close, size: 12, color: Colors.white),
                              ),
                            ),
                          ),
                        ],
                      );
                    }),
                  ],
                ),
              ),
              const SizedBox(height: 20),

              // Category dropdown
              Text(context.tr('ad_category'), style: const TextStyle(fontWeight: FontWeight.bold)),
              const SizedBox(height: 8),
              DropdownButtonFormField<int>(
                initialValue: _selectedCategoryId,
                decoration: const InputDecoration(contentPadding: EdgeInsets.symmetric(horizontal: 14, vertical: 12)),
                hint: const Text('Select Category'),
                items: categories.map((c) {
                  return DropdownMenuItem<int>(
                    value: c.id,
                    child: Text(c.name),
                  );
                }).toList(),
                onChanged: (val) => setState(() => _selectedCategoryId = val),
                validator: (val) => val == null ? 'Please select category' : null,
              ),
              const SizedBox(height: 16),

              // Language selector
              const Text('Advertisement Language', style: TextStyle(fontWeight: FontWeight.bold)),
              const SizedBox(height: 8),
              Row(
                children: [
                  _buildLangOption('en', 'English'),
                  const SizedBox(width: 8),
                  _buildLangOption('si', 'සිංහල'),
                  const SizedBox(width: 8),
                  _buildLangOption('ta', 'தமிழ்'),
                ],
              ),
              const SizedBox(height: 16),

              // Title
              Text(context.tr('ad_title'), style: const TextStyle(fontWeight: FontWeight.bold)),
              const SizedBox(height: 8),
              TextFormField(
                controller: _titleController,
                maxLength: 200,
                decoration: const InputDecoration(hintText: 'e.g. 2021 Toyota Premio G Superior'),
                validator: (val) => (val == null || val.trim().isEmpty) ? 'Title is required' : null,
              ),
              const SizedBox(height: 12),

              // Price
              Text(context.tr('ad_price'), style: const TextStyle(fontWeight: FontWeight.bold)),
              const SizedBox(height: 8),
              TextFormField(
                controller: _priceController,
                keyboardType: TextInputType.number,
                decoration: const InputDecoration(
                  hintText: 'Leave empty if negotiable',
                  prefixText: 'LKR ',
                ),
              ),
              const SizedBox(height: 16),

              // Location
              Text(context.tr('location'), style: const TextStyle(fontWeight: FontWeight.bold)),
              const SizedBox(height: 8),
              TextFormField(
                controller: _locationController,
                decoration: const InputDecoration(hintText: 'e.g. Colombo 03 / Kandy Town'),
                validator: (val) => (val == null || val.trim().isEmpty) ? 'Location is required' : null,
              ),
              const SizedBox(height: 16),

              // Description
              Text(context.tr('ad_description'), style: const TextStyle(fontWeight: FontWeight.bold)),
              const SizedBox(height: 8),
              TextFormField(
                controller: _descController,
                maxLines: 5,
                decoration: const InputDecoration(hintText: 'Describe item details, condition, terms...'),
                validator: (val) => (val == null || val.trim().isEmpty) ? 'Description is required' : null,
              ),
              const SizedBox(height: 20),

              // Seller Contact Details
              Text(context.tr('seller_details'), style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
              const SizedBox(height: 12),
              TextFormField(
                controller: _nameController,
                decoration: InputDecoration(
                  labelText: context.tr('contact_name'),
                  prefixIcon: const Icon(Icons.person_outline),
                ),
                validator: (val) => (val == null || val.trim().isEmpty) ? 'Name is required' : null,
              ),
              const SizedBox(height: 12),
              TextFormField(
                controller: _phoneController,
                keyboardType: TextInputType.phone,
                decoration: InputDecoration(
                  labelText: context.tr('contact_phone'),
                  prefixIcon: const Icon(Icons.phone_outlined),
                ),
                validator: (val) => (val == null || val.trim().isEmpty) ? 'Phone is required' : null,
              ),
              const SizedBox(height: 12),
              TextFormField(
                controller: _emailController,
                keyboardType: TextInputType.emailAddress,
                decoration: InputDecoration(
                  labelText: context.tr('contact_email'),
                  prefixIcon: const Icon(Icons.email_outlined),
                ),
                validator: (val) => (val == null || !val.contains('@')) ? 'Valid email is required' : null,
              ),
              const SizedBox(height: 20),

              // Premium Option
              Container(
                padding: const EdgeInsets.all(12),
                decoration: BoxDecoration(
                  color: AppColors.accentGold.withOpacity(0.12),
                  borderRadius: BorderRadius.circular(10),
                  border: Border.all(color: AppColors.accentGold),
                ),
                child: CheckboxListTile(
                  contentPadding: EdgeInsets.zero,
                  value: _premiumRequested,
                  activeColor: AppColors.accentGoldDark,
                  title: Text(
                    context.tr('request_premium'),
                    style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14),
                  ),
                  subtitle: const Text('Highlight ad with gold badge and top priority placement.'),
                  onChanged: (val) => setState(() => _premiumRequested = val ?? false),
                ),
              ),
              const SizedBox(height: 28),

              // Submit Button
              SizedBox(
                width: double.infinity,
                height: 52,
                child: ElevatedButton(
                  onPressed: _isSubmitting ? null : _submit,
                  child: _isSubmitting
                      ? const SizedBox(
                          width: 24,
                          height: 24,
                          child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
                        )
                      : Text(context.tr('submit_ad'), style: const TextStyle(fontSize: 16)),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildLangOption(String code, String label) {
    final isSelected = _language == code;
    return ChoiceChip(
      label: Text(label),
      selected: isSelected,
      selectedColor: AppColors.primary,
      labelStyle: TextStyle(
        color: isSelected ? Colors.white : null,
        fontWeight: isSelected ? FontWeight.bold : null,
      ),
      onSelected: (_) => setState(() => _language = code),
    );
  }
}

