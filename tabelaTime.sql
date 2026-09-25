SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";

SET NAMES utf8;

create database banco;
use banco;

CREATE TABLE IF NOT EXISTS tabela_time (
  id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  nome varchar(50) NOT NULL,
  dataFundacao date NOt NULL,
  estado varchar(40) NOT NULL,
  divisao char(1) NOT NULL,
  dataCadastro datetime NOT NULL,
  foto varchar(50)
) DEFAULT CHARSET=utf8 AUTO_INCREMENT=6;

INSERT INTO tabela_time (id, nome, dataFundacao, estado, divisao, dataCadastro, foto)
VALUES
(1, 'Corinthians', '1910-09-01', 'São Paulo', 'A', '2026-06-14 10:15:00', 'miniatura_corinthians.png'),
(2, 'Palmeiras', '1914-08-26', 'São Paulo', 'A', '2026-06-14 10:20:00', 'miniatura_palmeiras.png'),
(3, 'Flamengo', '1980-11-17', 'Rio de Janeiro', 'A', '2026-06-14 10:25:00', 'miniatura_flamengo.png'),
(4, 'Cruzeiro', '1921-01-02', 'Minas Gerais', 'A', '2026-06-14 10:30:00', 'miniatura_cruzeiro.png'),
(5, 'Sport Recife', '1905-05-13', 'Pernambuco', 'B', '2026-06-14 10:35:00', 'miniatura_sport_recife.png');