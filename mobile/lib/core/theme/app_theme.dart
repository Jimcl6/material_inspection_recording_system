import 'package:flutter/material.dart';
import 'package:google_fonts/google_fonts.dart';
import 'app_colors.dart';

class AppTheme {
  static ThemeData get lightTheme {
    final baseTextTheme = Typography.material2021().black;
    TextTheme figtreeTheme;
    try {
      figtreeTheme = GoogleFonts.figtreeTextTheme(baseTextTheme);
    } catch (_) {
      figtreeTheme = baseTextTheme.apply(fontFamily: 'sans-serif');
    }

    return ThemeData(
      useMaterial3: true,
      colorScheme: ColorScheme.fromSeed(
        seedColor: AppColors.indigo600,
        primary: AppColors.indigo600,
        onPrimary: AppColors.white,
        secondary: AppColors.indigo500,
        surface: AppColors.white,
        error: AppColors.red600,
      ),
      scaffoldBackgroundColor: AppColors.gray100,
      textTheme: figtreeTheme.copyWith(
        headlineLarge: figtreeTheme.headlineLarge?.copyWith(
          color: AppColors.gray900,
          fontWeight: FontWeight.bold,
        ),
        headlineMedium: figtreeTheme.headlineMedium?.copyWith(
          color: AppColors.gray900,
          fontWeight: FontWeight.bold,
        ),
        headlineSmall: figtreeTheme.headlineSmall?.copyWith(
          color: AppColors.gray900,
          fontWeight: FontWeight.w600,
        ),
        titleLarge: figtreeTheme.titleLarge?.copyWith(
          color: AppColors.gray900,
          fontWeight: FontWeight.w600,
          fontSize: 18,
        ),
        titleMedium: figtreeTheme.titleMedium?.copyWith(
          color: AppColors.gray900,
          fontWeight: FontWeight.w600,
          fontSize: 16,
        ),
        bodyLarge: figtreeTheme.bodyLarge?.copyWith(
          color: AppColors.gray800,
          fontSize: 15,
        ),
        bodyMedium: figtreeTheme.bodyMedium?.copyWith(
          color: AppColors.gray700,
          fontSize: 14,
        ),
        bodySmall: figtreeTheme.bodySmall?.copyWith(
          color: AppColors.gray500,
          fontSize: 12,
        ),
      ),
      appBarTheme: const AppBarTheme(
        backgroundColor: AppColors.white,
        foregroundColor: AppColors.gray900,
        elevation: 0,
        centerTitle: false,
        scrolledUnderElevation: 1,
        shadowColor: AppColors.gray200,
      ),
      cardTheme: CardThemeData(
        color: AppColors.white,
        elevation: 0.5,
        shadowColor: AppColors.gray300.withValues(alpha: 0.5),
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(12),
          side: const BorderSide(color: AppColors.gray200, width: 1),
        ),
        margin: EdgeInsets.zero,
      ),
      elevatedButtonTheme: ElevatedButtonThemeData(
        style: ElevatedButton.styleFrom(
          backgroundColor: AppColors.indigo600,
          foregroundColor: AppColors.white,
          minimumSize: const Size(0, 44),
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(8),
          ),
          textStyle: const TextStyle(
            fontSize: 14,
            fontWeight: FontWeight.w600,
          ),
          elevation: 0,
        ),
      ),
      outlinedButtonTheme: OutlinedButtonThemeData(
        style: OutlinedButton.styleFrom(
          foregroundColor: AppColors.gray700,
          backgroundColor: AppColors.white,
          side: const BorderSide(color: AppColors.gray300, width: 1),
          minimumSize: const Size(0, 44),
          padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(8),
          ),
          textStyle: const TextStyle(
            fontSize: 14,
            fontWeight: FontWeight.w600,
          ),
        ),
      ),
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: AppColors.white,
        contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
        hintStyle: const TextStyle(color: AppColors.gray400, fontSize: 14),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(8),
          borderSide: const BorderSide(color: AppColors.gray300, width: 1),
        ),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(8),
          borderSide: const BorderSide(color: AppColors.gray300, width: 1),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(8),
          borderSide: const BorderSide(color: AppColors.indigo600, width: 2),
        ),
        errorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(8),
          borderSide: const BorderSide(color: AppColors.red500, width: 1),
        ),
      ),
      dividerTheme: const DividerThemeData(
        color: AppColors.gray200,
        thickness: 1,
        space: 1,
      ),
    );
  }
}
