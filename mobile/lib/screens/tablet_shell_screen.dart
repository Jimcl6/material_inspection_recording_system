import 'package:flutter/material.dart';
import '../core/theme/app_colors.dart';
import '../providers/auth_controller.dart';
import '../providers/server_config_controller.dart';
import '../widgets/tablet/tablet_app_bar.dart';
import '../widgets/tablet/tablet_bottom_nav.dart';
import 'dashboard_screen.dart';
import 'scanner_sandbox_screen.dart';
import 'server_config_screen.dart';

class TabletShellScreen extends StatefulWidget {
  final AuthController authController;
  final ServerConfigController configController;

  const TabletShellScreen({
    super.key,
    required this.authController,
    required this.configController,
  });

  @override
  State<TabletShellScreen> createState() => _TabletShellScreenState();
}

class _TabletShellScreenState extends State<TabletShellScreen> {
  int _currentTabIndex = 0;

  final List<String> _tabTitles = [
    'Tablet Dashboard',
    'Inspection Records',
    'Bluetooth Scanner Test Sandbox',
    'Pending Approvals',
  ];

  void _onTabSelected(int index) {
    setState(() {
      _currentTabIndex = index;
    });
  }

  void _openModuleChooserSheet(bool isCreateMode) {
    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.transparent,
      isScrollControlled: true,
      builder: (ctx) {
        return Container(
          decoration: const BoxDecoration(
            color: AppColors.white,
            borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
          ),
          padding: const EdgeInsets.fromLTRB(28, 20, 28, 36),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        isCreateMode ? 'Create New Record' : 'Open Module Records',
                        style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: AppColors.gray900),
                      ),
                      const SizedBox(height: 2),
                      const Text(
                        'Select an operational manufacturing module',
                        style: TextStyle(fontSize: 13, color: AppColors.gray500),
                      ),
                    ],
                  ),
                  IconButton(
                    icon: const Icon(Icons.close, color: AppColors.gray500),
                    onPressed: () => Navigator.of(ctx).pop(),
                  ),
                ],
              ),
              const SizedBox(height: 20),
              Wrap(
                spacing: 12,
                runSpacing: 12,
                children: [
                  _buildModuleItem(
                    ctx,
                    title: 'Welding Checksheet',
                    subtitle: 'Phase 2 Pilot Module',
                    icon: Icons.description_outlined,
                    isPilot: true,
                    onTap: () {
                      Navigator.of(ctx).pop();
                      _onTabSelected(2); // Go to sandbox or welding
                    },
                  ),
                  _buildModuleItem(
                    ctx,
                    title: 'Annealing Checks',
                    subtitle: 'Furnace temperature and dampers',
                    icon: Icons.local_fire_department_outlined,
                    onTap: () => Navigator.of(ctx).pop(),
                  ),
                  _buildModuleItem(
                    ctx,
                    title: 'Temperature Records',
                    subtitle: 'Daily thermal logging',
                    icon: Icons.thermostat_outlined,
                    onTap: () => Navigator.of(ctx).pop(),
                  ),
                  _buildModuleItem(
                    ctx,
                    title: 'Torque Records',
                    subtitle: 'Multi-reading torque logs',
                    icon: Icons.build_outlined,
                    onTap: () => Navigator.of(ctx).pop(),
                  ),
                  _buildModuleItem(
                    ctx,
                    title: 'Magnetism Checksheet',
                    subtitle: 'Batches and checkpoints',
                    icon: Icons.search_outlined,
                    onTap: () => Navigator.of(ctx).pop(),
                  ),
                  _buildModuleItem(
                    ctx,
                    title: 'Material Monitoring',
                    subtitle: 'Part monitoring checksheets',
                    icon: Icons.category_outlined,
                    onTap: () => Navigator.of(ctx).pop(),
                  ),
                ],
              ),
            ],
          ),
        );
      },
    );
  }

  Widget _buildModuleItem(
    BuildContext context, {
    required String title,
    required String subtitle,
    required IconData icon,
    bool isPilot = false,
    required VoidCallback onTap,
  }) {
    return SizedBox(
      width: (MediaQuery.of(context).size.width - 92) / 3,
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(12),
        child: Container(
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            color: isPilot ? AppColors.indigo50 : AppColors.white,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: isPilot ? AppColors.indigo500 : AppColors.gray200),
          ),
          child: Row(
            children: [
              Icon(icon, color: isPilot ? AppColors.indigo600 : AppColors.gray700, size: 24),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      title,
                      style: TextStyle(
                        fontSize: 14,
                        fontWeight: FontWeight.bold,
                        color: isPilot ? AppColors.indigo700 : AppColors.gray900,
                      ),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      subtitle,
                      style: const TextStyle(fontSize: 11, color: AppColors.gray500),
                      overflow: TextOverflow.ellipsis,
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final auth = widget.authController;
    final server = widget.configController;

    Widget body;
    switch (_currentTabIndex) {
      case 0:
        body = DashboardScreen(
          user: auth.currentUser,
          onNavigateToScanner: () => _onTabSelected(2),
          onSelectModule: (mod) {
            if (mod == 'welding') {
              _onTabSelected(2);
            } else {
              ScaffoldMessenger.of(context).showSnackBar(
                SnackBar(content: Text('Module $mod is scheduled for Phase 3.')),
              );
            }
          },
        );
        break;
      case 2:
        body = const ScannerSandboxScreen();
        break;
      case 3:
        body = Center(
          child: Card(
            child: Padding(
              padding: const EdgeInsets.all(32),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  const Icon(Icons.verified_user_outlined, size: 48, color: AppColors.indigo600),
                  const SizedBox(height: 16),
                  const Text(
                    'Pending Approvals Queue',
                    style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                  ),
                  const SizedBox(height: 8),
                  const Text(
                    'Supervisor approval workflows will be connected in Phase 4.',
                    style: TextStyle(color: AppColors.gray500),
                  ),
                  const SizedBox(height: 20),
                  OutlinedButton(
                    onPressed: () => _onTabSelected(0),
                    child: const Text('Back to Dashboard'),
                  ),
                ],
              ),
            ),
          ),
        );
        break;
      default:
        body = const ScannerSandboxScreen();
    }

    return Scaffold(
      appBar: TabletAppBar(
        title: _tabTitles[_currentTabIndex],
        user: auth.currentUser,
        isServerConnected: server.isOnline ?? true,
        onSettingsTap: () => ServerConfigScreen.showAsDialog(context, server),
        onLogoutTap: () => auth.logout(),
      ),
      body: body,
      bottomNavigationBar: SafeArea(
        top: false,
        child: TabletBottomNav(
          currentIndex: _currentTabIndex,
          onTabSelected: _onTabSelected,
          onOpenRecordsSheet: () => _openModuleChooserSheet(false),
          onOpenNewRecordSheet: () => _openModuleChooserSheet(true),
        ),
      ),
    );
  }
}
