-- phpMyAdmin SQL Dump
-- version 4.8.5
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 06, 2026 at 08:02 AM
-- Server version: 10.1.38-MariaDB
-- PHP Version: 5.6.40

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `fc_1_data_db_testing`
--

-- --------------------------------------------------------

--
-- Table structure for table `annealing_checks`
--

CREATE TABLE `annealing_checks` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `item_code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `receiving_date` date NOT NULL,
  `supplier_lot_number` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantity` int(11) NOT NULL,
  `annealing_date` date NOT NULL,
  `machine_number` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `machine_setting` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pic_id` bigint(20) UNSIGNED NOT NULL COMMENT 'Person In Charge',
  `checked_by_id` bigint(20) UNSIGNED DEFAULT NULL,
  `verified_by_id` bigint(20) UNSIGNED DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED NOT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `annealing_checks`
--

INSERT INTO `annealing_checks` (`id`, `item_code`, `receiving_date`, `supplier_lot_number`, `quantity`, `annealing_date`, `machine_number`, `machine_setting`, `pic_id`, `checked_by_id`, `verified_by_id`, `created_by`, `updated_by`, `remarks`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'DF04100000-02', '2025-08-07', '1313123', 123123, '2025-09-01', '2312312', NULL, 3, 6, NULL, 1, 1, NULL, '2025-12-17 16:57:26', '2025-12-17 16:57:26', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inspections`
--

CREATE TABLE `inspections` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `title` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `status` enum('pending','in_progress','completed','failed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `inspections`
--

INSERT INTO `inspections` (`id`, `user_id`, `title`, `description`, `status`, `notes`, `created_at`, `updated_at`) VALUES
(1, 4, 'Incidunt iste et.', 'Ab omnis reiciendis ipsam est facilis et ea. Culpa harum sint eos reprehenderit reiciendis vel ea. Dolor dolores in quaerat id.', 'pending', 'Quam qui aspernatur omnis quod aut id dolore aut. Qui tempore iusto exercitationem nam ipsam harum voluptas. Quas suscipit nemo sequi earum qui ipsa accusantium.', '2025-09-28 04:54:32', '2025-10-06 01:24:23'),
(2, 3, 'Voluptatibus neque repellat quia corporis.', 'Adipisci soluta a dolor doloremque. Distinctio consequuntur eum debitis quisquam nesciunt quos. Quo earum accusamus officia velit molestiae.', 'in_progress', 'Quia libero sequi dolores accusamus incidunt est. Voluptatem sunt vel officia laboriosam nemo.', '2025-08-05 04:10:41', '2025-12-06 11:03:03'),
(3, 7, 'Rerum odio et.', 'Autem vel expedita est est nulla consequatur. Cum similique voluptatem ut. Totam similique perferendis iste autem nihil consequatur. Aut amet tempora et aut voluptas doloribus cum odit.', 'failed', NULL, '2025-11-02 00:36:25', '2025-12-08 21:01:32'),
(4, 7, 'Tenetur quia dolores.', 'Alias sit aut totam hic quis. Ut laborum laudantium sint sit tempora non. Excepturi voluptatum dicta libero ut quod aliquam quod. Sapiente eius vel quia aut libero aut.', 'failed', NULL, '2025-12-05 18:49:42', '2025-12-13 12:22:07'),
(5, 6, 'Est sit libero.', 'Vitae ut earum numquam architecto et. Autem et iste harum non non nam. Et eos est consectetur ullam officia. Occaecati ab accusamus perspiciatis eius provident quo voluptatem. Dolorem officiis et non culpa numquam corporis laborum fugit.', 'pending', NULL, '2025-08-25 08:28:30', '2025-09-09 09:36:16'),
(6, 5, 'Et quis omnis.', 'Saepe voluptas quaerat nostrum quia voluptatibus id maiores. Similique omnis ea ut aperiam doloribus voluptatem consequatur. Rerum enim autem nemo velit voluptates. Ratione expedita eaque aut totam.', 'pending', 'Aspernatur et omnis velit consequatur rerum quidem. Corrupti voluptas et quo.', '2025-08-31 19:43:54', '2025-10-04 18:20:57'),
(7, 4, 'Dicta facilis qui atque illo.', 'Perspiciatis unde quia optio magni error officiis. Suscipit rem cum labore et non nulla laborum omnis. Tenetur animi repudiandae numquam eum et facilis aut. Dolorum dolor quam quis omnis neque eveniet.', 'completed', 'Enim quidem dolor recusandae rerum qui quia. Eos id illum ad quis cum est aut. Eius quia aut necessitatibus itaque ea.', '2025-11-11 11:38:04', '2025-12-03 05:16:16'),
(8, 2, 'Consequatur perferendis qui enim dolorem magnam.', 'Incidunt distinctio inventore voluptas maxime ipsum consequatur minima. Et omnis et dolor voluptates nobis illum. Sit voluptatem explicabo repudiandae delectus rerum autem sed. Eius consequatur omnis non recusandae aliquid.', 'pending', 'Magni fuga tenetur ex est mollitia magnam. Ipsa amet labore totam non porro et distinctio.', '2025-06-21 19:10:40', '2025-12-06 16:22:11'),
(9, 5, 'Voluptatibus inventore sequi perferendis iusto quibusdam.', 'Deserunt esse eius libero voluptates aut distinctio a. Consequatur aut dolorum aspernatur eveniet qui. Culpa est perferendis nostrum quia.', 'completed', 'Explicabo laboriosam qui quo laboriosam minus suscipit. Saepe in dolorum et quisquam. Laudantium molestiae sit rerum ut suscipit itaque id.', '2025-11-19 21:33:38', '2025-12-06 10:04:20'),
(10, 4, 'Omnis beatae alias aut et.', 'Qui rem omnis iste vitae vel. Quos mollitia ipsa eum. Sint omnis repellendus nostrum sunt.', 'completed', 'Qui placeat omnis nam illum quia. Maiores ratione voluptate iusto sit maxime optio. Unde voluptatibus totam deserunt animi repellendus.', '2025-06-30 15:24:36', '2025-07-25 11:02:16'),
(11, 8, 'Explicabo animi et velit.', 'Nulla sequi aliquam velit perferendis excepturi non. Error odio veritatis aut eos officiis qui error. Consequatur et totam quo vitae.', 'in_progress', 'Quos numquam voluptatem est ullam. Corporis reiciendis sapiente minima. Delectus est tenetur officia.', '2025-08-14 17:16:46', '2025-09-18 20:20:54'),
(12, 9, 'Amet sed praesentium.', 'Commodi voluptatem adipisci et quia consequatur aspernatur. Iusto praesentium quia et excepturi cupiditate quia quos. Nisi sunt magnam provident a eveniet sed et quidem. Qui dolorem sit tempore laboriosam officiis.', 'failed', NULL, '2025-10-08 07:27:16', '2025-11-08 12:50:42'),
(13, 8, 'Quae totam quae dolor adipisci consequuntur.', 'Modi quo facilis doloribus non voluptates. Ut quae cupiditate dolores reprehenderit. Accusamus dicta voluptate ut quis. Consequuntur vero dolorem vel corrupti numquam. Aliquid corporis perferendis tenetur rerum velit est ex itaque.', 'pending', 'Laudantium voluptas ex inventore cumque est fuga. Itaque architecto accusantium reprehenderit eligendi laudantium. Deleniti dicta rerum possimus fugit odit voluptatem ad.', '2025-08-05 01:35:06', '2025-09-14 08:31:58'),
(14, 3, 'Dolor provident repellat labore et.', 'Blanditiis exercitationem odit magnam est consequatur. Corrupti ab in iusto voluptatem. Quisquam est commodi minus facere dignissimos eos qui enim.', 'pending', 'Est sint molestiae et provident tempore quia repudiandae. Voluptas est ea deserunt nesciunt distinctio tempora.', '2025-09-22 00:09:34', '2025-12-10 14:48:43'),
(15, 2, 'Hic soluta neque quo et.', 'Blanditiis eos ex dolor rerum aut molestiae. Qui id quibusdam numquam corporis omnis. Natus molestiae minima voluptatem tempora incidunt. Id laudantium vero provident alias nostrum ipsa. Et praesentium odit in rerum incidunt vel autem.', 'pending', 'Aut quam placeat impedit explicabo voluptas quis rem. Ducimus et et reprehenderit provident beatae. Odio est rerum ipsam et nihil.', '2025-11-30 23:41:35', '2025-12-12 15:59:43'),
(16, 3, 'Et sed nesciunt dolores neque.', 'Hic voluptates voluptatem eligendi sapiente. Voluptatem harum quibusdam nisi commodi officia dicta voluptatem. Sequi natus laborum quas autem non eius.', 'in_progress', 'Tenetur qui harum dolores. Quis aliquam est explicabo vel dolorem.', '2025-12-01 11:58:15', '2025-12-14 10:17:02'),
(17, 4, 'Fugiat aut quia explicabo.', 'Dolorem qui distinctio dolorem quia omnis est. Omnis a consequatur non et architecto odio aut. Qui minima illum reiciendis et nesciunt debitis fugit nesciunt. Corrupti et asperiores est quasi.', 'pending', NULL, '2025-10-26 12:43:32', '2025-12-10 07:58:44'),
(18, 4, 'Enim et autem et.', 'Dolores nisi sit velit. Officia quod dolor pariatur provident est quidem animi. Saepe id dolores mollitia architecto molestias nihil quo.', 'failed', 'Molestiae distinctio sunt est qui ut adipisci quibusdam. Aspernatur qui nisi vitae et.', '2025-06-30 07:25:58', '2025-09-01 04:35:46'),
(19, 7, 'Aut aut a magni.', 'Nihil aut molestias incidunt similique hic modi. Natus magnam non alias aliquam. Quos culpa sit odio eos maiores qui reprehenderit dolorum.', 'pending', 'Ut enim inventore suscipit et molestiae et animi. Ipsum aut optio et aut consequatur quod iste id. Consectetur omnis omnis qui sed culpa.', '2025-09-27 02:30:35', '2025-10-11 15:39:25'),
(20, 2, 'Nihil autem iure illo corrupti est.', 'Non sapiente explicabo perspiciatis autem nulla. Nihil quia vero nobis consequatur ut sunt quis nulla. Commodi cupiditate eaque reprehenderit nobis.', 'in_progress', 'Quisquam temporibus beatae veritatis autem possimus autem. Enim maiores nesciunt maxime maiores et reiciendis.', '2025-09-11 03:31:49', '2025-11-01 18:56:34'),
(21, 9, 'Voluptatem est laudantium.', 'Asperiores est cum consequatur voluptatem. Velit rerum ab aspernatur natus. Aliquid deserunt quo minima repudiandae tempore magnam sequi possimus. Aut alias autem perferendis veritatis adipisci recusandae.', 'in_progress', 'Consequuntur fugit atque qui sapiente. Quaerat itaque ex porro fugiat omnis vitae aut.', '2025-06-23 07:58:11', '2025-06-24 20:24:04'),
(22, 4, 'Non dolor voluptatum consequatur fugit doloribus.', 'Eligendi vel sint nisi atque qui quidem vitae doloribus. Adipisci perferendis fugit sit deleniti rerum.', 'completed', 'Perspiciatis ea quae tempore assumenda ut dolores aut. Ad et sit sunt omnis dolores et.', '2025-07-21 04:34:55', '2025-11-23 03:50:50'),
(23, 1, 'Pariatur illum temporibus voluptatem qui est.', 'Dolores quaerat placeat quia porro. Saepe nihil debitis culpa voluptatem sapiente omnis velit minima. Optio qui ea tempore nam minima. Praesentium aut est explicabo ut.', 'completed', NULL, '2025-08-16 02:21:51', '2025-12-01 05:33:53'),
(24, 7, 'Maiores est sed fuga exercitationem.', 'Quibusdam dolorum qui ut amet perspiciatis quia repellat. Nihil sed laudantium ut et deserunt repudiandae quibusdam. Omnis necessitatibus ea corrupti molestias quos praesentium distinctio.', 'failed', 'Sed eum dolores voluptate nesciunt qui quae unde nam. Non cum quibusdam occaecati voluptatem quaerat. Eveniet tenetur odit praesentium libero rerum.', '2025-11-11 22:45:57', '2025-12-02 09:39:12'),
(25, 5, 'Voluptas cupiditate consequatur eum.', 'Nobis sed consequatur explicabo excepturi assumenda qui blanditiis. Et rem iste facilis itaque recusandae sed nam perspiciatis. Voluptatem amet id quis. Sed a cupiditate tempore facere eaque iusto.', 'failed', 'Commodi aliquam voluptatum sit sed porro ut. Sit quos maxime dolor. Voluptatem soluta quisquam sit illo harum necessitatibus.', '2025-07-02 15:01:23', '2025-12-07 23:12:11'),
(26, 5, 'Porro dicta a odit aut.', 'Rem similique quaerat et debitis. Optio quasi dignissimos animi ipsum est veritatis. Vel fuga excepturi minima vero.', 'failed', 'Non officiis illum ut dolorem cumque. Omnis harum amet voluptas amet perferendis sit sed. Et dolorum non et facilis odio ut suscipit cupiditate.', '2025-08-31 15:07:21', '2025-09-27 14:56:14'),
(27, 5, 'Sed nihil voluptas.', 'Et debitis hic est corrupti. Perspiciatis exercitationem vero animi accusamus. Iste suscipit velit provident est repellat inventore voluptates. Exercitationem ducimus eius qui assumenda sit.', 'completed', 'Voluptatum ut voluptas voluptatem non dolores ut. Cupiditate maiores at sed cumque vitae. Optio accusamus non facere.', '2025-12-12 23:05:58', '2025-12-13 23:41:37'),
(28, 4, 'Voluptate magnam necessitatibus.', 'Ducimus qui quo et voluptatibus. Dolore soluta asperiores dolorem illo et. Cum provident natus accusamus modi deserunt distinctio voluptas corrupti. Voluptates omnis quia nobis sed fuga reprehenderit.', 'completed', 'Natus velit reprehenderit aliquid rerum voluptatibus. Mollitia similique sed ut aliquid nemo optio. Est sed et consequatur assumenda.', '2025-10-26 08:49:23', '2025-11-11 14:55:51'),
(29, 1, 'Mollitia saepe fugit.', 'Quia magnam non corporis sequi sed quae. Voluptates iusto eum odio nostrum necessitatibus ratione.', 'failed', NULL, '2025-06-29 06:38:36', '2025-07-18 12:36:31'),
(30, 3, 'Ex et qui aspernatur non non.', 'Atque inventore consectetur unde dolore illo voluptates. Est blanditiis nam et quidem qui ut. Eos ut autem eum sapiente amet labore.', 'failed', 'Voluptatibus sed voluptatibus veritatis perferendis. Saepe vero aspernatur est magnam porro. Qui harum cum atque.', '2025-07-08 07:59:16', '2025-07-21 13:37:46'),
(31, 9, 'Repellendus vitae in.', 'Quis ut et omnis iusto assumenda. Iste et consequatur quia temporibus nemo perspiciatis. Natus voluptatum quia consectetur.', 'failed', 'Quibusdam est fugiat suscipit odit laudantium sint occaecati eos. Dolorum commodi deleniti hic veniam provident atque. Nisi nihil ratione aut quia.', '2025-11-07 09:43:24', '2025-11-14 22:51:06'),
(32, 8, 'Aliquid non molestiae similique quaerat at.', 'Ut odit rerum velit laudantium nulla minima debitis. Aut accusamus ut aut qui rerum soluta quia nobis. Tenetur molestiae omnis quod magni nihil.', 'failed', NULL, '2025-08-31 12:28:56', '2025-11-07 04:29:54'),
(33, 8, 'Quam rerum est quo rerum.', 'Velit architecto consectetur aliquam velit voluptas alias ut. Beatae blanditiis illo labore est et. Sint perspiciatis dolor sapiente.', 'failed', 'Soluta corrupti est eaque architecto et nulla id. Debitis vitae maxime in.', '2025-09-17 14:21:28', '2025-12-02 14:22:47'),
(34, 4, 'Odio necessitatibus sapiente quo aut nemo.', 'Necessitatibus quam autem sed aliquam deleniti debitis. Et est enim unde delectus quod. Deserunt ea magni veritatis sunt voluptatem fugit. Fugiat rerum repudiandae tempora.', 'failed', 'Est et laudantium excepturi rerum et et. Tempore corporis quae laboriosam quos quis. Sit aut ea quod et distinctio.', '2025-07-27 02:35:24', '2025-11-01 11:05:20'),
(35, 7, 'Ratione nisi molestiae molestias quam.', 'Dolore magni voluptatem veritatis. Ea non beatae dolores id aliquid aspernatur. Et voluptate neque voluptatem delectus repellat. Ipsam ducimus sint et repellat sed.', 'completed', NULL, '2025-10-10 02:05:42', '2025-10-13 07:57:43'),
(36, 6, 'Rerum voluptas laudantium ut ut.', 'Odio hic aperiam vero aspernatur rerum sed. Vel aut est incidunt magni. Magnam voluptatum nihil iure fugiat.', 'pending', 'Corporis ratione vero quos adipisci ex. Ut et dolorum molestiae.', '2025-11-26 07:58:22', '2025-12-01 13:38:09'),
(37, 4, 'Quod consequatur voluptatum excepturi.', 'Velit et dignissimos sunt molestiae. Totam voluptatem in praesentium autem iste sed. Facere dolor aliquid adipisci et omnis.', 'failed', 'Deleniti omnis maxime sit. Et sed quaerat et.', '2025-07-15 16:39:16', '2025-09-28 00:31:49'),
(38, 9, 'Debitis eveniet quam ab.', 'Tempora nulla adipisci est. Ab est sed quod ad necessitatibus est. Modi quaerat qui provident quidem.', 'failed', NULL, '2025-10-25 20:07:31', '2025-10-31 21:37:08'),
(39, 1, 'Quibusdam laboriosam et deleniti quod.', 'Et dolor nam quasi aut. Maiores similique natus sed et a beatae dolores. Harum laboriosam nam aut.', 'failed', 'Nemo minima repellat sint vel ratione qui eveniet. Sit iusto ratione sed soluta. Quia est ea vel corporis sed maxime.', '2025-10-21 20:50:26', '2025-11-01 08:32:36'),
(40, 5, 'Et ex omnis vel.', 'Temporibus autem nesciunt dolor omnis deserunt pariatur recusandae velit. Corporis minima nisi enim ipsam eius.', 'failed', NULL, '2025-09-23 16:27:45', '2025-10-19 12:20:22'),
(41, 4, 'Quis animi ex rerum.', 'Animi ad provident et quia et et. Magni voluptas modi laudantium odit aperiam. Animi et ipsum rerum minus rerum et.', 'failed', 'Explicabo quam iusto voluptate nihil velit a. Necessitatibus rerum magnam necessitatibus.', '2025-07-26 13:54:05', '2025-10-12 01:08:37'),
(42, 10, 'Delectus sed debitis at.', 'Labore temporibus laborum sequi explicabo. Ut minima id nam necessitatibus minus culpa rem. Eaque rerum sed ea sit nihil. Corporis numquam placeat itaque et.', 'completed', 'Blanditiis cupiditate aut ut. Ipsam sit saepe molestias consequuntur ducimus. Sit sunt ipsa est repudiandae fugit nam eum reiciendis.', '2025-10-21 13:57:45', '2025-11-12 05:15:38'),
(43, 7, 'Praesentium deserunt ut atque.', 'Blanditiis deserunt vitae voluptatem. Esse officiis labore quas ut et. Id eum quia eos nam et.', 'completed', 'Ex non dignissimos veniam sit. Molestias doloribus omnis vitae optio quis aut sed.', '2025-09-02 01:50:08', '2025-10-30 14:24:46'),
(44, 9, 'Aut odio aut dolorem.', 'Iste sed iusto et aut veniam. Eum minus fuga est doloribus quia. Totam blanditiis voluptatem suscipit qui autem eos corrupti.', 'failed', 'Et omnis sunt dolor vitae ipsam molestiae. Vitae et enim sed ut ipsa aut. A sed id numquam ut ratione fugit.', '2025-10-21 20:59:51', '2025-10-23 10:03:37'),
(45, 6, 'Animi eos et at ut.', 'Odit eligendi est rerum voluptas recusandae rem. Porro maxime voluptate accusantium. Aut labore itaque distinctio tempore praesentium.', 'in_progress', 'Eaque excepturi ab voluptatum velit harum. Iure id debitis dolores assumenda consequuntur ut et rerum.', '2025-07-03 11:18:16', '2025-08-15 06:07:50'),
(46, 5, 'Aut velit dolorum aut aliquid occaecati.', 'Voluptatem harum consequatur minus fuga. Suscipit et maxime et.', 'failed', 'Explicabo quis odit explicabo sed qui ut. Omnis voluptatem officiis dolores ut. Et voluptatem nobis odio earum vel.', '2025-11-30 02:13:55', '2025-12-04 20:53:10'),
(47, 8, 'Accusantium unde et adipisci similique pariatur.', 'Aut sint natus libero fugiat quae. Quis ratione dolorum ad facere ducimus est. Incidunt recusandae accusantium excepturi error laboriosam. Nihil dolorum libero laborum quos suscipit molestiae esse.', 'failed', NULL, '2025-11-30 07:21:42', '2025-12-12 19:08:16'),
(48, 8, 'Velit voluptatem consectetur nihil.', 'Facere repellat ducimus optio sapiente porro. Mollitia ut et sint aliquid voluptatem velit maiores.', 'in_progress', NULL, '2025-08-24 10:32:53', '2025-09-15 11:55:48'),
(49, 7, 'Esse alias quod laboriosam omnis sit.', 'Cumque quod corporis velit corrupti error aut occaecati. Pariatur provident accusantium porro minima nemo. Qui laborum numquam quis libero.', 'failed', 'Rerum temporibus totam quaerat qui sit et. Molestias inventore dolore doloribus non itaque.', '2025-12-13 15:58:57', '2025-12-14 15:20:47'),
(50, 2, 'Architecto nihil asperiores.', 'Fugiat voluptas qui voluptate velit illum et quae. Quia omnis magni ipsum et repellendus ducimus molestiae. Error qui aut eos quo. Odio harum omnis unde quidem nobis voluptas.', 'failed', 'Quia et et quod consequatur molestias iste perferendis. Et voluptatibus dolores ipsam odio. Sit iure ipsam quo ut voluptate exercitationem qui.', '2025-07-13 08:28:50', '2025-12-14 08:40:05');

-- --------------------------------------------------------

--
-- Table structure for table `materials`
--

CREATE TABLE `materials` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `specification` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `unit_of_measure` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `material_inspection`
--

CREATE TABLE `material_inspection` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2014_10_12_000000_create_users_table', 1),
(2, '2014_10_12_100000_create_password_resets_table', 1),
(3, '2019_08_19_000000_create_failed_jobs_table', 1),
(4, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(5, '2025_12_15_063034_create_roles_table', 1),
(6, '2025_12_15_231424_create_inspections_table', 1),
(7, '2025_12_15_234241_create_materials_table', 1),
(8, '2025_12_15_234320_create_material_inspection_table', 1),
(9, '2025_12_16_020000_create_temp_records_table', 1),
(10, '2025_12_16_020001_create_torque_records_table', 1),
(11, '2025_12_16_020002_create_modification_logs_table', 1),
(12, '2025_12_16_000001_create_annealing_tables', 2),
(13, '2024_03_15_000000_create_temperature_records_table', 3),
(14, '2025_12_18_082832_add_date_and_model_series_to_torque_records_table', 3),
(15, '2025_12_18_083424_add_date_and_model_series_to_torque_records_table', 4),
(16, '2025_12_18_083514_add_date_and_model_series_to_torque_records_table', 4);

-- --------------------------------------------------------

--
-- Table structure for table `modification_logs`
--

CREATE TABLE `modification_logs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `prod_date` date NOT NULL,
  `col_4m` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `col_line` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_code` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `item_for_modification` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nature_of_change` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `col_from` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `col_to` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `material_lot_no` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_serial` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `end_serial` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `recorded_by` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `source_of_info` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `approved_by` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `job_no_transfer_order` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `col_remarks` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `productionbatches`
--

CREATE TABLE `productionbatches` (
  `BatchID` int(11) NOT NULL,
  `ProductionDate` date NOT NULL,
  `LetterCode` varchar(5) NOT NULL,
  `QRCode` varchar(20) NOT NULL,
  `MaterialLotNumber` varchar(50) NOT NULL,
  `ProduceQty` int(11) NOT NULL,
  `JobNumber` varchar(50) NOT NULL,
  `TotalQty` int(11) NOT NULL,
  `Remarks` text,
  `CreatedAt` datetime DEFAULT CURRENT_TIMESTAMP,
  `UpdatedAt` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `productionbatches`
--

INSERT INTO `productionbatches` (`BatchID`, `ProductionDate`, `LetterCode`, `QRCode`, `MaterialLotNumber`, `ProduceQty`, `JobNumber`, `TotalQty`, `Remarks`, `CreatedAt`, `UpdatedAt`) VALUES
(1, '2026-01-05', 'A', '131232', '13231231', 12, '31312', 0, NULL, '2026-01-05 09:19:57', '2026-01-05 09:19:57');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `slug`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Administrator', 'admin', 'Has full access to all features', '2025-12-15 18:34:56', '2025-12-15 18:34:56'),
(2, 'User', 'user', 'Standard user with limited access', '2025-12-15 18:34:56', '2025-12-15 18:34:56'),
(3, 'Inspector', 'inspector', 'Can perform inspections', '2025-12-15 18:34:56', '2025-12-15 18:34:56');

-- --------------------------------------------------------

--
-- Table structure for table `temperature_readings`
--

CREATE TABLE `temperature_readings` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `annealing_check_id` bigint(20) UNSIGNED NOT NULL,
  `reading_time` time NOT NULL,
  `temperature` decimal(8,2) NOT NULL,
  `recorded_by` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `temperature_readings`
--

INSERT INTO `temperature_readings` (`id`, `annealing_check_id`, `reading_time`, `temperature`, `recorded_by`, `created_at`, `updated_at`) VALUES
(1, 1, '07:40:00', '120.00', 1, '2025-12-17 16:57:26', '2025-12-17 16:57:26'),
(2, 1, '09:40:00', '123.00', 1, '2025-12-17 16:57:26', '2025-12-17 16:57:26');

-- --------------------------------------------------------

--
-- Table structure for table `temp_records`
--

CREATE TABLE `temp_records` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `date` date NOT NULL,
  `model_series` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `solder_model` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `line_assigned` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `control_no` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `equipment_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `process_assigned` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `person_in_charge` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `time_am` time DEFAULT NULL,
  `temp_am` decimal(5,2) DEFAULT NULL,
  `time_pm` time DEFAULT NULL,
  `temp_pm` decimal(5,2) DEFAULT NULL,
  `col_remarks` text COLLATE utf8mb4_unicode_ci,
  `checked_by` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `temp_records`
--

INSERT INTO `temp_records` (`id`, `date`, `model_series`, `solder_model`, `line_assigned`, `control_no`, `equipment_type`, `process_assigned`, `person_in_charge`, `time_am`, `temp_am`, `time_pm`, `temp_pm`, `col_remarks`, `checked_by`, `created_at`, `updated_at`) VALUES
(1, '2025-12-18', '123123223', '123123', 'MULTI LINE', '123123', 'Soldering Iron', '13123', '1233232', '10:15:00', '132.00', '15:19:00', '132.00', NULL, 'JUAN DELA CRUZ', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `torque_records`
--

CREATE TABLE `torque_records` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `date` date NOT NULL,
  `model_series` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `driver_model` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `driver_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `line_assigned` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `control_no` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `screw_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `process_assigned` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `person_in_charge` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `time_am` time DEFAULT NULL,
  `torque_am` decimal(6,2) DEFAULT NULL,
  `time_pm` time DEFAULT NULL,
  `torque_pm` decimal(6,2) DEFAULT NULL,
  `col_remarks` text COLLATE utf8mb4_unicode_ci,
  `checked_by` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `torque_records`
--

INSERT INTO `torque_records` (`id`, `date`, `model_series`, `driver_model`, `driver_type`, `line_assigned`, `control_no`, `screw_type`, `process_assigned`, `person_in_charge`, `time_am`, `torque_am`, `time_pm`, `torque_pm`, `col_remarks`, `checked_by`, `created_at`, `updated_at`) VALUES
(1, '2025-12-15', 'SAMPLE', '1323123', 'Manual', 'MULTILINE', '13123', 'M4x16', '2', 'JUAN DELA CRUZ', '07:04:00', '32.00', '13:09:00', '32.00', NULL, 'JOHN DOE', NULL, NULL),
(2, '2025-12-22', '423414', '423414', 'Automatic', 'MULTI LINE', '13123', 'M4x16', '1', 'IVAN HOW', '02:20:00', '123.00', '05:23:00', '132.00', NULL, 'JOHN DOE', NULL, NULL),
(3, '2025-12-22', '123312321', '312123231', 'Automatic', 'MULTI LINE', '13123', 'M4x16', '13123', 'IVAN HOW', '08:44:00', '123.00', '13:42:00', '132.00', NULL, 'JUAN DELA CRUZ', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL DEFAULT '2',
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `role_id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`) VALUES
(1, 1, 'Admin User', 'admin@example.com', '2025-12-15 18:34:56', '$2y$10$Vz6.ikztSbGAerexjHo3Pu8xN5T4SpecILGLpCMkG37QUSU99ysA.', NULL, '2025-12-15 18:34:56', '2025-12-15 18:34:56'),
(2, 2, 'Jacinthe Kling MD', 'baumbach.opal@example.net', '2025-12-15 18:34:56', '$2y$10$llCBlaRMBvMxwa.5.clcyOuuqk.32Btb4PPvp9E9dPFJ7PVnR2Vam', 'pQdwzdYrO1', '2025-12-15 18:34:57', '2025-12-15 18:34:57'),
(3, 2, 'Lilly Larkin', 'rraynor@example.org', '2025-12-15 18:34:56', '$2y$10$YdU2VpnzKgDoWEyuN/zwQ.ndoa54gJhz9TQjooH0H3uwfQxF.cZiu', 'bKLkq8hyGa', '2025-12-15 18:34:57', '2025-12-15 18:34:57'),
(4, 2, 'Dr. Aniyah Homenick', 'trenton46@example.net', '2025-12-15 18:34:56', '$2y$10$Veba4ye/zz6S64himn/bu.3ff68WGmRdwcczD6blARKKuy8GJAGhS', '34gvwIBP2m', '2025-12-15 18:34:57', '2025-12-15 18:34:57'),
(5, 2, 'Marta Champlin III', 'roel04@example.net', '2025-12-15 18:34:57', '$2y$10$3DNOP/Bnf/Gpn2Tv5haijuDNSLz6yBUgRJNXXQ7ivQq2gR2TVy4ze', '04NVhaHdgA', '2025-12-15 18:34:57', '2025-12-15 18:34:57'),
(6, 2, 'Veda DuBuque V', 'johnson.carrie@example.org', '2025-12-15 18:34:57', '$2y$10$nQO/K.CHldiBwKPKmis5EeAzxNdTMdFHkUMJvqo/lzbiePuY/MLla', 'rlt1AAZBbS', '2025-12-15 18:34:57', '2025-12-15 18:34:57'),
(7, 2, 'Mr. Fermin DuBuque', 'vickie73@example.net', '2025-12-15 18:34:57', '$2y$10$F8CJIFPqLalrQfLRA1SkruKs2H7weiiG4yzieBPM3TmFRVGeSUfrG', '00IF62P3Ec', '2025-12-15 18:34:57', '2025-12-15 18:34:57'),
(8, 2, 'Queenie Satterfield Sr.', 'will.lexi@example.com', '2025-12-15 18:34:57', '$2y$10$qs5OkCC3quNHlJnT6WDA5uFkZlPbCSX0tm0rhHfg.dIyaX51qtKji', '5WjK0HCNrR', '2025-12-15 18:34:57', '2025-12-15 18:34:57'),
(9, 2, 'Prof. Dusty Ziemann II', 'ycormier@example.com', '2025-12-15 18:34:57', '$2y$10$pF4WGBy.Ok8qVM/VZ5meYeKspURCtB2PfCTIAiy3PFea9qkQUg8pa', 'msH8v4KM1V', '2025-12-15 18:34:57', '2025-12-15 18:34:57'),
(10, 2, 'Jammie Cronin DVM', 'ctreutel@example.net', '2025-12-15 18:34:57', '$2y$10$Zs6vtysHHXXDVm6d1dHP/ezPzfpYtdZCc0zamOZHRzWU8jESHNtTG', 'jQ35yzZ0si', '2025-12-15 18:34:57', '2025-12-15 18:34:57');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `annealing_checks`
--
ALTER TABLE `annealing_checks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `annealing_checks_item_code_index` (`item_code`),
  ADD KEY `annealing_checks_supplier_lot_number_index` (`supplier_lot_number`),
  ADD KEY `annealing_checks_annealing_date_index` (`annealing_date`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `inspections`
--
ALTER TABLE `inspections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `inspections_user_id_foreign` (`user_id`);

--
-- Indexes for table `materials`
--
ALTER TABLE `materials`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `material_inspection`
--
ALTER TABLE `material_inspection`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `modification_logs`
--
ALTER TABLE `modification_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `productionbatches`
--
ALTER TABLE `productionbatches`
  ADD PRIMARY KEY (`BatchID`),
  ADD KEY `IDX_Production_QR_Lot` (`QRCode`,`MaterialLotNumber`),
  ADD KEY `IDX_Production_Date` (`ProductionDate`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_slug_unique` (`slug`);

--
-- Indexes for table `temperature_readings`
--
ALTER TABLE `temperature_readings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `temperature_readings_annealing_check_id_foreign` (`annealing_check_id`),
  ADD KEY `temperature_readings_reading_time_index` (`reading_time`);

--
-- Indexes for table `temp_records`
--
ALTER TABLE `temp_records`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `torque_records`
--
ALTER TABLE `torque_records`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_email_unique` (`email`),
  ADD KEY `users_role_id_foreign` (`role_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `annealing_checks`
--
ALTER TABLE `annealing_checks`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inspections`
--
ALTER TABLE `inspections`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `materials`
--
ALTER TABLE `materials`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `material_inspection`
--
ALTER TABLE `material_inspection`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `modification_logs`
--
ALTER TABLE `modification_logs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `productionbatches`
--
ALTER TABLE `productionbatches`
  MODIFY `BatchID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `temperature_readings`
--
ALTER TABLE `temperature_readings`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `temp_records`
--
ALTER TABLE `temp_records`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `torque_records`
--
ALTER TABLE `torque_records`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `inspections`
--
ALTER TABLE `inspections`
  ADD CONSTRAINT `inspections_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `temperature_readings`
--
ALTER TABLE `temperature_readings`
  ADD CONSTRAINT `temperature_readings_annealing_check_id_foreign` FOREIGN KEY (`annealing_check_id`) REFERENCES `annealing_checks` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
