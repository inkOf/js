CREATE TABLE IF NOT EXISTS `tovars_07718` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(80) NOT NULL,
  `price` decimal(10,2) default NULL,
  `weight` int(10) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT INTO `tovars_07718` (`title`, `price`, `weight`) VALUES
	('Квас "Тарас"', '12.10', 750),
	('Печеные квасцы', '14.23', 1000),
	('Burda', '10.86', 800),
	('Каша-малаша', '24.78', 999),
	('Red Burda', '13.25', 800),
	('Цукерки "Живинка"', '18.50', 1000),
	('Синий угорь', '32.16', 1000),
	('Limo "Peach"', '11.50', 75),
	('Shvargettsaali', '43.89', 1500),
	('Шамбала', '13.00', 50),
	('Сэндвич с курицей', '7.50', 250),
	('Анис', '20.00', 45),
	('Бублик "Бублик"', '3.42', 200),
	('Печеньки', '17.60', 1000);