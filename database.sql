CREATE DATABASE IF NOT EXISTS `ong` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `ong`;

-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 12/09/2026 às 20:40
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `ong`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `adocoes`
--

CREATE TABLE `adocoes` (
  `id_adocao` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_animal` int(11) NOT NULL,
  `data` date DEFAULT NULL,
  `status` enum('pendente','aprovado','rejeitado','concluido') DEFAULT 'pendente',
  `observacoes` text DEFAULT NULL,
  `descricao` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Despejando dados para a tabela `adocoes`
--

INSERT INTO `adocoes` (`id_adocao`, `id_usuario`, `id_animal`, `data`, `status`, `observacoes`, `descricao`) VALUES
(1, 5, 2, '2026-09-07', 'aprovado', 'vira buscar hoje as 15horas', 'vao ser muito felizes'),
(2, 6, 1, '2026-09-08', 'pendente', NULL, 'vao ser muito felizes'),
(3, 9, 6, '2026-09-09', 'rejeitado', 'usuario sera excluido', 'Solicitação de adoção'),
(4, 11, 7, '2026-09-11', 'pendente', NULL, 'Solicitação de adoção'),
(5, 11, 5, '2026-09-11', 'pendente', NULL, 'Solicitação de adoção');

-- --------------------------------------------------------

--
-- Estrutura para tabela `animais`
--

CREATE TABLE `animais` (
  `id_animal` int(11) NOT NULL,
  `nome` varchar(30) DEFAULT NULL,
  `especie` enum('Gato','Cão') DEFAULT NULL,
  `porte` enum('pequeno','médio','grande') DEFAULT NULL,
  `data_acolhimento` date DEFAULT NULL,
  `idade_aparente` enum('filhote','adulto','idoso') DEFAULT NULL,
  `saude` text DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Despejando dados para a tabela `animais`
--

INSERT INTO `animais` (`id_animal`, `nome`, `especie`, `porte`, `data_acolhimento`, `idade_aparente`, `saude`, `foto`) VALUES
(1, 'Pitoco', 'Cão', 'médio', '2026-01-20', 'adulto', 'muito saudavel e esperto', 'uploads/animais/animal_6aa49d4252205.jpg'),
(2, 'Mininha', 'Gato', 'pequeno', '2026-02-15', 'filhote', 'saudavel ligeira', 'uploads/animais/animal_6aa49da49ef5b.jpg'),
(3, 'Pepito', 'Cão', 'pequeno', '2026-02-17', 'filhote', 'muito saudavel ', 'uploads/animais/animal_6aa49d38e8041.jpg'),
(4, 'Gatão', 'Gato', 'pequeno', '2026-03-15', 'adulto', 'saudavel ligeira', 'uploads/animais/animal_6aa49d9b32e8d.jpg'),
(5, 'Chiquinha', 'Cão', 'pequeno', '2026-03-23', 'filhote', 'muito saudavel', 'uploads/animais/animal_6aa49cc20ec92.jpg'),
(6, 'Princesa', 'Gato', 'pequeno', '2026-04-10', 'filhote', 'muito saudavel', 'uploads/animais/animal_6aa49c84ebcdd.jpg'),
(7, 'xiquita', 'Gato', 'pequeno', '2026-09-11', 'filhote', 'ela é RN esta perfeita!', 'uploads/animais/animal_6aa49b869d999.jpg');

-- --------------------------------------------------------

--
-- Estrutura para tabela `departamentos`
--

CREATE TABLE `departamentos` (
  `id_departamento` int(11) NOT NULL,
  `nome` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Despejando dados para a tabela `departamentos`
--

INSERT INTO `departamentos` (`id_departamento`, `nome`) VALUES
(1, 'Financeiro'),
(2, 'Veterinario'),
(3, 'Operacional'),
(4, 'Doadores'),
(5, 'Administrativo');

-- --------------------------------------------------------

--
-- Estrutura para tabela `doacoes`
--

CREATE TABLE `doacoes` (
  `id_doacao` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `tipo` enum('dinheiro','item') DEFAULT NULL,
  `quantidade` int(11) DEFAULT NULL,
  `valor` decimal(10,2) DEFAULT NULL,
  `data` date DEFAULT NULL,
  `descricao` text DEFAULT NULL,
  `status` enum('pendente','confirmado','cancelado') DEFAULT 'pendente',
  `data_agendamento` datetime DEFAULT NULL,
  `confirmado_por` int(11) DEFAULT NULL,
  `data_confirmacao` datetime DEFAULT NULL,
  `comprovante` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Despejando dados para a tabela `doacoes`
--

INSERT INTO `doacoes` (`id_doacao`, `id_usuario`, `tipo`, `quantidade`, `valor`, `data`, `descricao`, `status`, `data_agendamento`, `confirmado_por`, `data_confirmacao`, `comprovante`) VALUES
(1, 3, 'dinheiro', NULL, 100.00, '2026-08-15', 'doação via pix na cc', 'confirmado', NULL, 11, '2026-09-12 15:07:20', NULL),
(2, 4, 'item', 2, NULL, '2026-09-02', 'doação de 2 sacos 15kg ração premium', 'confirmado', NULL, 11, '2026-09-12 15:07:47', NULL),
(4, 9, 'item', 3, 0.00, '2026-09-09', 'pacote de ração 12kg premium carne', 'pendente', NULL, NULL, NULL, NULL),
(5, 11, 'dinheiro', 0, 100.00, '2026-09-11', 'pix', 'confirmado', NULL, 11, '2026-09-12 15:07:25', NULL),
(6, 6, 'dinheiro', NULL, 100.00, '2026-09-12', 'via pix', 'confirmado', NULL, 11, '2026-09-12 15:07:02', NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `id_departamento` int(11) DEFAULT NULL,
  `nome` varchar(30) DEFAULT NULL,
  `data_nascimento` date DEFAULT NULL,
  `endereco` varchar(50) DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `email` varchar(50) DEFAULT NULL,
  `senha` varchar(255) DEFAULT NULL,
  `token_recuperacao` varchar(255) DEFAULT NULL,
  `expira_token` datetime DEFAULT NULL,
  `nivel` int(11) DEFAULT 4
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `id_departamento`, `nome`, `data_nascimento`, `endereco`, `telefone`, `email`, `senha`, `token_recuperacao`, `expira_token`, `nivel`) VALUES
(1, 1, 'Valéria Campos', '1979-04-10', 'Rua Margarida 74', '13996581247', 'valeria_vet@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 2),
(2, 2, 'Margarida Silva', '1963-06-12', 'Rua Bromélia 32', '13985692367', 'maginhalinda@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 4),
(3, 3, 'João Paulo', '1973-10-24', 'Rua Rosas 125', '13996325876', 'jaopaulo_oficial@hotmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 4),
(4, 3, 'Thoma Aquino', '1981-07-13', 'Rua Gira Sol 205', '13970146589', 'tomasquino@hotmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 4),
(5, 4, 'Paula Ortéga', '1981-09-21', 'Rua Paraiba 45', '13974412983', 'paula_ortega@gmail.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 3),
(6, 4, 'Abilio Dinniz', '1948-08-06', 'Rua Guaiatuva 63', '13996325841', 'albidinniz@hotmail.com', '$2y$10$D2ODEdvfzU1CwcfywqikqeSTLeWXKIxMrAJZR0Tx/3LpCXTGQogdy', NULL, NULL, 3),


--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `adocoes`
--
ALTER TABLE `adocoes`
  ADD PRIMARY KEY (`id_adocao`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_animal` (`id_animal`);

--
-- Índices de tabela `animais`
--
ALTER TABLE `animais`
  ADD PRIMARY KEY (`id_animal`);

--
-- Índices de tabela `departamentos`
--
ALTER TABLE `departamentos`
  ADD PRIMARY KEY (`id_departamento`);

--
-- Índices de tabela `doacoes`
--
ALTER TABLE `doacoes`
  ADD PRIMARY KEY (`id_doacao`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Índices de tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD KEY `id_departamento` (`id_departamento`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `adocoes`
--
ALTER TABLE `adocoes`
  MODIFY `id_adocao` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `animais`
--
ALTER TABLE `animais`
  MODIFY `id_animal` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de tabela `departamentos`
--
ALTER TABLE `departamentos`
  MODIFY `id_departamento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `doacoes`
--
ALTER TABLE `doacoes`
  MODIFY `id_doacao` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `adocoes`
--
ALTER TABLE `adocoes`
  ADD CONSTRAINT `adocoes_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`),
  ADD CONSTRAINT `adocoes_ibfk_2` FOREIGN KEY (`id_animal`) REFERENCES `animais` (`id_animal`);

--
-- Restrições para tabelas `doacoes`
--
ALTER TABLE `doacoes`
  ADD CONSTRAINT `doacoes_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);

--
-- Restrições para tabelas `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`id_departamento`) REFERENCES `departamentos` (`id_departamento`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
