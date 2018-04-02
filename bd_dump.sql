-- phpMyAdmin SQL Dump
-- version 4.4.15.10
-- https://www.phpmyadmin.net
--
-- Хост: localhost
-- Время создания: Апр 02 2018 г., 17:45
-- Версия сервера: 5.5.52-MariaDB-cll-lve
-- Версия PHP: 5.4.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `resag464_testrestapi`
--

-- --------------------------------------------------------

--
-- Структура таблицы `coupons`
--

CREATE TABLE IF NOT EXISTS `coupons` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `code` varchar(255) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `coupons`
--

INSERT INTO `coupons` (`id`, `name`, `code`) VALUES
(1, 'simple', '8OL7W9RCQ1'),
(3, 'simple', '9M69YWNMJ8'),
(5, 'simple', 'C2WQQ5G9KH'),
(7, 'multu', 'N17U0VHOLR'),
(9, 'multu', 'NQT51TI1VM'),
(11, 'multu', 'DGLFLSEVID'),
(13, 'mega', 'Q59FEE2UK6'),
(15, 'mega', '7KDYH3M3VJ');

-- --------------------------------------------------------

--
-- Структура таблицы `merchants`
--

CREATE TABLE IF NOT EXISTS `merchants` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` varchar(255) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `merchants`
--

INSERT INTO `merchants` (`id`, `name`, `description`) VALUES
(1, 'mts', 'com'),
(2, 'megafon', 'com'),
(3, 'aliexpress', 'China web shop'),
(4, 'alibaba', 'China web shop'),
(5, 'test1', 'qwe'),
(7, 'test1', 'test1'),
(8, 'test2', 'test2');

-- --------------------------------------------------------

--
-- Структура таблицы `relations`
--

CREATE TABLE IF NOT EXISTS `relations` (
  `id` int(11) NOT NULL,
  `cid` int(11) NOT NULL,
  `muid` int(11) NOT NULL,
  `type` varchar(10) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `relations`
--

INSERT INTO `relations` (`id`, `cid`, `muid`, `type`) VALUES
(1, 9, 4, 'users'),
(2, 7, 5, 'users'),
(3, 15, 3, 'merchants'),
(4, 13, 4, 'merchants'),
(5, 1, 4, 'merchants');

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `datetime` datetime NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id`, `token`, `name`, `email`, `password`, `datetime`) VALUES
(2, 'X66800988890112983045ac1176ab3b928.07902327', 'fixnewuser1', 'fixtest1@ex.ex', '123', '2018-04-02 03:06:00'),
(4, 'U85868147019863818245ac19da8ee6a41.09002146', 'test3', 'test3@ex.ex', 'e10adc3949ba59abbe56e057f20f883e', '2018-04-02 01:01:04'),
(5, 'U78514405634578841605ac19da8ee6b05.51765420', 'test4', 'test4@ex.ex', 'caf1a3dfb505ffed0d024130f58c5cfa', '2018-04-02 03:06:00'),
(6, 'U8681289620127744005ac210a82176b2.98503413', 'newuser11', 'utest11@ex.ex', 'e10adc3949ba59abbe56e057f20f883e', '2018-04-02 01:01:04'),
(7, 'U6440297576246804485ac210a8217782.60048496', 'newuser22', 'test22@ex.ex', 'caf1a3dfb505ffed0d024130f58c5cfa', '2018-04-02 03:06:00'),
(23, 'U43608120824053104645ac22284beaf15.20955155', 'newuser33', 'fixed@fix.fu', 'e10adc3949ba59abbe56e057f20f883e', '2018-04-02 01:01:04');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `coupons`
--
ALTER TABLE `coupons`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `merchants`
--
ALTER TABLE `merchants`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `relations`
--
ALTER TABLE `relations`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `coupons`
--
ALTER TABLE `coupons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=17;
--
-- AUTO_INCREMENT для таблицы `merchants`
--
ALTER TABLE `merchants`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=9;
--
-- AUTO_INCREMENT для таблицы `relations`
--
ALTER TABLE `relations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=6;
--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=25;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
