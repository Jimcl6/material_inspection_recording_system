import 'package:flutter/material.dart';
import '../../core/theme/app_colors.dart';
import '../../models/user_model.dart';

class TabletAppBar extends StatelessWidget implements PreferredSizeWidget {
  final String title;
  final UserModel? user;
  final bool isServerConnected;
  final VoidCallback onSettingsTap;
  final VoidCallback onLogoutTap;

  const TabletAppBar({
    super.key,
    required this.title,
    this.user,
    this.isServerConnected = true,
    required this.onSettingsTap,
    required this.onLogoutTap,
  });

  @override
  Size get preferredSize => const Size.fromHeight(64);

  @override
  Widget build(BuildContext context) {
    final initial = (user?.name.isNotEmpty == true) ? user!.name[0].toUpperCase() : 'U';

    return Container(
      height: 64,
      padding: const EdgeInsets.symmetric(horizontal: 24),
      decoration: const BoxDecoration(
        color: AppColors.white,
        border: Border(bottom: BorderSide(color: AppColors.gray200, width: 1)),
      ),
      child: Row(
        children: [
          // Brand Logo & Title
          Row(
            children: [
              Container(
                width: 36,
                height: 36,
                decoration: BoxDecoration(
                  color: AppColors.indigo50,
                  borderRadius: BorderRadius.circular(8),
                  border: Border.all(color: AppColors.indigo200),
                ),
                alignment: Alignment.center,
                child: const Icon(
                  Icons.precision_manufacturing_rounded,
                  color: AppColors.indigo600,
                  size: 22,
                ),
              ),
              const SizedBox(width: 12),
              Column(
                mainAxisAlignment: MainAxisAlignment.center,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text(
                    'ICRS / MIRS',
                    style: TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.bold,
                      color: AppColors.gray900,
                      letterSpacing: 0.5,
                    ),
                  ),
                  Text(
                    title,
                    style: const TextStyle(
                      fontSize: 12,
                      fontWeight: FontWeight.w500,
                      color: AppColors.indigo600,
                    ),
                  ),
                ],
              ),
            ],
          ),

          const Spacer(),

          // Server Connection Badge
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
            decoration: BoxDecoration(
              color: isServerConnected ? AppColors.emerald50 : AppColors.amber50,
              borderRadius: BorderRadius.circular(16),
              border: Border.all(
                color: isServerConnected ? AppColors.emerald500.withValues(alpha: 0.3) : AppColors.amber500.withValues(alpha: 0.4),
              ),
            ),
            child: Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                Container(
                  width: 8,
                  height: 8,
                  decoration: BoxDecoration(
                    shape: BoxShape.circle,
                    color: isServerConnected ? AppColors.emerald500 : AppColors.amber500,
                  ),
                ),
                const SizedBox(width: 6),
                Text(
                  isServerConnected ? 'LAN Online' : 'Check Server',
                  style: TextStyle(
                    fontSize: 12,
                    fontWeight: FontWeight.w600,
                    color: isServerConnected ? AppColors.emerald700 : AppColors.amber700,
                  ),
                ),
              ],
            ),
          ),

          const SizedBox(width: 16),

          // Server Config Button
          IconButton(
            icon: const Icon(Icons.settings_outlined, color: AppColors.gray600),
            onPressed: onSettingsTap,
            tooltip: 'Server LAN Configuration',
            splashRadius: 20,
          ),

          const SizedBox(width: 8),

          // User Profile Pill & Menu
          if (user != null) ...[
            Container(
              height: 40,
              padding: const EdgeInsets.only(left: 4, right: 12),
              decoration: BoxDecoration(
                color: AppColors.gray50,
                borderRadius: BorderRadius.circular(20),
                border: Border.all(color: AppColors.gray200),
              ),
              child: Row(
                children: [
                  CircleAvatar(
                    radius: 15,
                    backgroundColor: AppColors.indigo100,
                    child: Text(
                      initial,
                      style: const TextStyle(
                        fontSize: 13,
                        fontWeight: FontWeight.bold,
                        color: AppColors.indigo700,
                      ),
                    ),
                  ),
                  const SizedBox(width: 8),
                  Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        user!.name,
                        style: const TextStyle(
                          fontSize: 13,
                          fontWeight: FontWeight.w600,
                          color: AppColors.gray900,
                        ),
                      ),
                      if (user!.role != null)
                        Text(
                          user!.role!.name,
                          style: const TextStyle(
                            fontSize: 10,
                            color: AppColors.gray500,
                          ),
                        ),
                    ],
                  ),
                ],
              ),
            ),
            const SizedBox(width: 12),
            IconButton(
              icon: const Icon(Icons.logout, color: AppColors.gray500, size: 20),
              onPressed: onLogoutTap,
              tooltip: 'Sign Out',
              splashRadius: 20,
            ),
          ],
        ],
      ),
    );
  }
}
