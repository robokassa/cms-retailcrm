-- phpMyAdmin SQL Dump
-- version 5.0.2
-- https://www.phpmyadmin.net/
--
-- Хост: localhost
-- Время создания: Май 27 2021 г., 08:59
-- Версия сервера: 8.0.20
-- Версия PHP: 7.4.7

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `mysklad`
--

-- --------------------------------------------------------

--
-- Структура таблицы `rtl_links`
--

CREATE TABLE `rtl_links` (
  `id` bigint NOT NULL,
  `client_id` varchar(36) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL DEFAULT '0',
  `invoice_id` varchar(36) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `data` text,
  `ts` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `active` tinyint(1) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Структура таблицы `rtl_users`
--

CREATE TABLE `rtl_users` (
  `id` bigint NOT NULL,
  `client_id` varchar(36) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `rtl_url` varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `rtl_api_key` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `robo_shop` varchar(30) DEFAULT NULL,
  `robo_test` tinyint(1) DEFAULT '1',
  `robo_key_1` varchar(50) DEFAULT NULL,
  `robo_key_2` varchar(50) DEFAULT NULL,
  `robo_key_test_1` varchar(50) DEFAULT NULL,
  `robo_key_test_2` varchar(50) DEFAULT NULL,
  `robo_fisk` tinyint(1) DEFAULT '0',
  `robo_country` varchar(30) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT 'RU'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `rtl_links`
--
ALTER TABLE `rtl_links`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_user` (`client_id`);

--
-- Индексы таблицы `rtl_users`
--
ALTER TABLE `rtl_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `client_id` (`client_id`),
  ADD KEY `client_id_2` (`client_id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `rtl_links`
--
ALTER TABLE `rtl_links`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблицы `rtl_users`
--
ALTER TABLE `rtl_users`
  MODIFY `id` bigint NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
