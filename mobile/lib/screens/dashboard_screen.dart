import 'package:flutter/material.dart';
import '../core/theme/app_colors.dart';
import '../models/user_model.dart';

class DashboardScreen extends StatelessWidget {
  final UserModel? user;
  final VoidCallback onNavigateToScanner;
  final ValueChanged<String> onSelectModule;

  const DashboardScreen({
    super.key,
    required this.user,
    required this.onNavigateToScanner,
    required this.onSelectModule,
  });

  @override
  Widget build(BuildContext context) {
    final operatorName = user?.name.split(' ').first ?? 'Operator';

    return Scaffold(
      backgroundColor: AppColors.gray100,
      body: SingleChildScrollView(
        padding: const EdgeInsets.symmetric(horizontal: 32, vertical: 24),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Welcome Section
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text(
                      'TABLET WORKSPACE',
                      style: TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.bold,
                        color: AppColors.indigo600,
                        letterSpacing: 1.0,
                      ),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      'Good day, $operatorName',
                      style: const TextStyle(
                        fontSize: 24,
                        fontWeight: FontWeight.bold,
                        color: AppColors.gray900,
                      ),
                    ),
                    const SizedBox(height: 4),
                    const Text(
                      'Select an inspection module below, or test wireless peripherals in the scanner sandbox.',
                      style: TextStyle(fontSize: 13, color: AppColors.gray500),
                    ),
                  ],
                ),

                // Fast Scanner Sandbox Trigger
                ElevatedButton.icon(
                  onPressed: onNavigateToScanner,
                  icon: const Icon(Icons.qr_code_scanner, size: 20),
                  label: const Text('Open Scanner Sandbox'),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: AppColors.indigo600,
                    foregroundColor: AppColors.white,
                    minimumSize: const Size(0, 44),
                  ),
                ),
              ],
            ),

            const SizedBox(height: 24),

            // Stat Cards Row
            Row(
              children: [
                Expanded(
                  child: _buildStatCard(
                    title: "TODAY'S TOTAL",
                    value: '14',
                    subtitle: 'Inspection records submitted',
                    icon: Icons.check_circle_outline,
                    color: AppColors.indigo600,
                  ),
                ),
                const SizedBox(width: 16),
                Expanded(
                  child: _buildStatCard(
                    title: 'PENDING APPROVAL',
                    value: '3',
                    subtitle: 'Awaiting supervisor sign-off',
                    icon: Icons.pending_actions,
                    color: AppColors.amber500,
                  ),
                ),
                const SizedBox(width: 16),
                Expanded(
                  child: _buildStatCard(
                    title: 'PERIPHERAL STATUS',
                    value: 'HID Active',
                    subtitle: 'Bluetooth scanner ready',
                    icon: Icons.bluetooth_connected,
                    color: AppColors.emerald500,
                  ),
                ),
              ],
            ),

            const SizedBox(height: 28),

            const Text(
              'Inspection Recording Modules',
              style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: AppColors.gray900),
            ),
            const SizedBox(height: 12),

            // Modules Grid
            GridView.count(
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              crossAxisCount: 3,
              crossAxisSpacing: 16,
              mainAxisSpacing: 16,
              childAspectRatio: 2.1,
              children: [
                _buildModuleTile(
                  title: 'Welding Checksheet',
                  subtitle: 'Multi-sample inspection (Phase 2 Pilot)',
                  icon: Icons.description_outlined,
                  color: AppColors.indigo600,
                  tag: 'PILOT MODULE',
                  onTap: () => onSelectModule('welding'),
                ),
                _buildModuleTile(
                  title: 'Annealing Checks',
                  subtitle: 'Receiving, furnace temp & damper logs',
                  icon: Icons.local_fire_department_outlined,
                  color: Colors.orange.shade700,
                  onTap: () => onSelectModule('annealing'),
                ),
                _buildModuleTile(
                  title: 'Temperature Records',
                  subtitle: 'Daily thermal logs and shift readings',
                  icon: Icons.thermostat_outlined,
                  color: AppColors.emerald700,
                  onTap: () => onSelectModule('temperature'),
                ),
                _buildModuleTile(
                  title: 'Torque Records',
                  subtitle: 'Multi-reading torque tolerance verification',
                  icon: Icons.build_outlined,
                  color: Colors.amber.shade800,
                  onTap: () => onSelectModule('torque'),
                ),
                _buildModuleTile(
                  title: 'Magnetism Checksheet',
                  subtitle: 'Batches, checkpoints & letter codes',
                  icon: Icons.search_outlined,
                  color: Colors.purple.shade700,
                  onTap: () => onSelectModule('magnetism'),
                ),
                _buildModuleTile(
                  title: 'Material Monitoring',
                  subtitle: 'Part monitoring and sub-lot fields',
                  icon: Icons.category_outlined,
                  color: const Color(0xFFBE123C),
                  onTap: () => onSelectModule('material'),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildStatCard({
    required String title,
    required String value,
    required String subtitle,
    required IconData icon,
    required Color color,
  }) {
    return Container(
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        color: AppColors.white,
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: AppColors.gray200),
        boxShadow: const [
          BoxShadow(
            color: Color(0x0A000000),
            blurRadius: 8,
            offset: Offset(0, 2),
          ),
        ],
      ),
      child: Row(
        children: [
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  title,
                  style: const TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.bold,
                    color: AppColors.gray500,
                    letterSpacing: 0.5,
                  ),
                ),
                const SizedBox(height: 6),
                Text(
                  value,
                  style: const TextStyle(
                    fontSize: 22,
                    fontWeight: FontWeight.bold,
                    color: AppColors.gray900,
                  ),
                ),
                const SizedBox(height: 4),
                Text(
                  subtitle,
                  style: const TextStyle(fontSize: 12, color: AppColors.gray500),
                ),
              ],
            ),
          ),
          Container(
            padding: const EdgeInsets.all(10),
            decoration: BoxDecoration(
              color: color.withValues(alpha: 0.1),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Icon(icon, color: color, size: 24),
          ),
        ],
      ),
    );
  }

  Widget _buildModuleTile({
    required String title,
    required String subtitle,
    required IconData icon,
    required Color color,
    String? tag,
    required VoidCallback onTap,
  }) {
    return Card(
      child: InkWell(
        onTap: onTap,
        borderRadius: BorderRadius.circular(12),
        child: Padding(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Container(
                    padding: const EdgeInsets.all(8),
                    decoration: BoxDecoration(
                      color: color.withValues(alpha: 0.1),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Icon(icon, color: color, size: 20),
                  ),
                  if (tag != null)
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                      decoration: BoxDecoration(
                        color: AppColors.indigo50,
                        borderRadius: BorderRadius.circular(4),
                        border: Border.all(color: AppColors.indigo200),
                      ),
                      child: Text(
                        tag,
                        style: const TextStyle(
                          fontSize: 9,
                          fontWeight: FontWeight.bold,
                          color: AppColors.indigo700,
                        ),
                      ),
                    ),
                ],
              ),
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    title,
                    style: const TextStyle(
                      fontSize: 14,
                      fontWeight: FontWeight.bold,
                      color: AppColors.gray900,
                    ),
                  ),
                  const SizedBox(height: 2),
                  Text(
                    subtitle,
                    style: const TextStyle(fontSize: 11, color: AppColors.gray500),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                  ),
                ],
              ),
            ],
          ),
        ),
      ),
    );
  }
}
