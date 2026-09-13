CREATE TABLE usuarios (
    id_usuario INT NOT NULL AUTO_INCREMENT,
    id_departamento INT,
    nome VARCHAR(30),
    data_nascimento DATE,
    endereco VARCHAR(50),
    telefone VARCHAR(20),
    email VARCHAR(50),
    PRIMARY KEY (id_usuario),
    FOREIGN KEY (id_departamento) REFERENCES departamentos (id_departamento)
) DEFAULT CHARSET = utf8;

CREATE TABLE departamentos (
    id_departamento INT NOT NULL AUTO_INCREMENT,
    nome VARCHAR(30),
    PRIMARY KEY (id_departamento)
) DEFAULT CHARSET = utf8;

CREATE TABLE animais (
    id_animal INT NOT NULL AUTO_INCREMENT,
    id_usuario INT,
    nome VARCHAR(30),
    especie ENUM('Gato', 'Cão'),
    porte ENUM('pequeno', 'médio', 'grande'),
    data_acolhimento DATE,
    idade_aparente ENUM('filhote', 'adulto', 'idoso'),
    saude TEXT,
    PRIMARY KEY (id_animal),
    FOREIGN KEY (id_usuario) REFERENCES usuarios (id_usuario)
) DEFAULT CHARSET utf8;

CREATE TABLE adocoes (
    id_adocao INT NOT NULL AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    id_animal INT NOT NULL,
    data DATE,
    descricao TEXT,
    PRIMARY KEY (id_adocao),
    FOREIGN KEY (id_usuario) REFERENCES usuarios (id_usuario),
    FOREIGN KEY (id_animal) REFERENCES animais (id_animal)
) DEFAULT CHARSET utf8;

CREATE TABLE doacoes (
    id_doacao INT NOT NULL AUTO_INCREMENT,
    id_usuario INT NOT NULL,
    tipo ENUM('dinheiro', 'item'),
    quantidade INT,
    valor DECIMAL(10, 2),
    data DATE,
    descricao TEXT,
    PRIMARY KEY (id_doacao),
    FOREIGN KEY (id_usuario) REFERENCES usuarios (id_usuario)
) DEFAULT CHARSET utf8;

INSERT INTO
    departamentos
VALUES (DEFAULT, 'Financeiro'),
    (DEFAULT, 'Veterinario'),
    (DEFAULT, 'Operacional'),
    (DEFAULT, 'Doadores');

INSERT INTO
    usuarios
VALUES (
        DEFAULT,
        1,
        'Valéria Campos',
        '1979-04-10',
        'Rua Margarida 74',
        '13996581247',
        'valeria_vet@gmail.com'
    ),
    (
        DEFAULT,
        2,
        'Margarida Silva',
        '1963-06-12',
        'Rua Bromélia 32',
        '13985692367',
        'maginhalinda@gmail.com'
    ),
    (
        DEFAULT,
        3,
        'João Paulo',
        '1973-10-24',
        'Rua Rosas 125',
        '13996325876',
        'jaopaulo_oficial@hotmail.com'
    ),
    (
        DEFAULT,
        3,
        'Thoma Aquino',
        '1981-07-13',
        'Rua Gira Sol 205',
        '13970146589',
        'tomasquino@hotmail.com'
    );

INSERT INTO
    usuarios
VALUES (
        DEFAULT,
        4,
        'Paula Ortéga',
        '1981-09-21',
        'Rua Paraiba 45',
        '13974412983',
        'paula_ortega@gmail.com'
    ),
    (
        DEFAULT,
        4,
        'Abilio Dinniz',
        '1948-08-06',
        'Rua Guaiatuva 63',
        '13996325841',
        'albidinniz@hotmail.com'
    );

SELECT * FROM usuarios;

INSERT INTO
    animais
VALUES (
        DEFAULT,
        6,
        'Pitoco',
        'Cão',
        'médio',
        '2026-01-20',
        'adulto',
        'muito saudavel e esperto'
    ),
    (
        DEFAULT,
        NULL,
        'Mininha',
        'Gato',
        'pequeno',
        '2026-02-15',
        'filhote',
        'saudavel ligeira'
    ),
    (
        DEFAULT,
        NULL,
        'Pepito',
        'Cão',
        'pequeno',
        '2026-02-17',
        'filhote',
        'muito saudavel '
    ),
    (
        DEFAULT,
        NULL,
        'Gatão',
        'Gato',
        'pequeno',
        '2026-03-15',
        'adulto',
        'saudavel ligeira'
    ),
    (
        DEFAULT,
        NULL,
        'Chiquinha',
        'Cão',
        'pequeno',
        '2026-03-23',
        'filhote',
        'muito saudavel'
    ),
    (
        DEFAULT,
        NULL,
        'Princesa',
        'Gato',
        'pequeno',
        '2026-04-10',
        'filhote',
        'muito saudavel'
    );

SELECT * FROM animais;

INSERT INTO
    adocoes
VALUES (
        DEFAULT,
        5,
        2,
        '2026-09-07',
        'vao ser muito felizes'
    ),
    (
        DEFAULT,
        6,
        1,
        '2026-09-08',
        'vao ser muito felizes'
    );

SELECT nome, especie, data_acolhimento
FROM animais
WHERE
    porte = 'pequeno';

INSERT INTO
    doacoes
VALUES (
        DEFAULT,
        3,
        'dinheiro',
        NULL,
        '100.00',
        '2026-08-15',
        'doação via pix na cc'
    ),
    (
        DEFAULT,
        4,
        'item',
        2,
        NULL,
        '2026-09-02',
        'doação de 2 sacos 15kg ração premium'
    );

SELECT * FROM doacoes;

ALTER TABLE usuarios ADD COLUMN senha VARCHAR(255) AFTER email;

-- Atualizar senhas existentes (use '123456' como senha padrão para teste)
UPDATE usuarios
SET
    senha = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
WHERE
    id_usuario > 0;

SELECT * FROM adocoes;

SELECT
    a.id_adocao,
    u.nome as usuario_nome,
    an.nome as animal_nome,
    a.data,
    a.descricao
FROM
    adocoes a
    JOIN usuarios u ON a.id_usuario = u.id_usuario
    JOIN animais an ON a.id_animal = an.id_animal;

SELECT * FROM usuarios;

-- Adicionar campo senha
ALTER TABLE usuarios ADD COLUMN senha VARCHAR(255) AFTER email;

SELECT * FROM usuarios;
-- Adicionar campo status
ALTER TABLE adocoes
ADD COLUMN status ENUM(
    'pendente',
    'aprovado',
    'rejeitado',
    'concluido'
) DEFAULT 'pendente' AFTER data;

SELECT * FROM doacoes;
-- Admin (nível 1)
UPDATE usuarios SET nivel = 1 WHERE id_usuario = 11;

-- Gestores (nível 2)
UPDATE usuarios SET nivel = 2 WHERE email = 'valeria_vet@gmail.com';

-- Voluntários (nível 3)
UPDATE usuarios SET nivel = 3 WHERE email = 'maria@email.com';

-- Usuários comuns (nível 4) - padrão
UPDATE usuarios SET nivel = 4 WHERE nivel IS NULL;

SELECT * FROM departamentos;

SELECT * FROM usuarios;

INSERT INTO departamentos VALUES (DEFAULT, 'Administrativo');

INSERT INTO
    usuarios
VALUES (
        DEFAULT,
        5,
        'Leandro Motta',
        '1982-04-12',
        'Rua Espanha 810',
        '13996488301',
        'lemmuzmotta@gmail.com',
        '123',
        1
    );

DELETE FROM usuarios WHERE id_usuario = 10;

use ong;

SELECT * FROM usuarios;

ALTER TABLE usuarios
ADD COLUMN token_recuperacao VARCHAR(255) DEFAULT NULL AFTER senha;

ALTER TABLE usuarios
ADD COLUMN expira_token DATETIME DEFAULT NULL AFTER token_recuperacao;

DESC adocoes;

ALTER TABLE usuarios
ADD COLUMN expira_token DATETIME DEFAULT NULL AFTER token_recuperacao;

ALTER TABLE animais ADD COLUMN foto VARCHAR(255) AFTER saude;

SELECT * FROM usuarios;

ALTER TABLE adocoes
ADD COLUMN status ENUM(
    'pendente',
    'aprovado',
    'rejeitado',
    'concluido'
) DEFAULT 'pendente' AFTER descricao;

ALTER TABLE adocoes
ADD COLUMN observacoes TEXT DEFAULT NULL AFTER status;

DELETE FROM usuarios WHERE id_usuario = 9;

DESC doacoes;

ALTER TABLE doacoes
ADD COLUMN status ENUM(
    'pendente',
    'confirmado',
    'cancelado'
) DEFAULT 'pendente' AFTER descricao;

ALTER TABLE doacoes
ADD COLUMN data_agendamento DATETIME DEFAULT NULL AFTER status;

ALTER TABLE doacoes
ADD COLUMN confirmado_por INT DEFAULT NULL AFTER data_agendamento;

ALTER TABLE doacoes
ADD COLUMN data_confirmacao DATETIME DEFAULT NULL AFTER confirmado_por;

ALTER TABLE doacoes
ADD COLUMN comprovante VARCHAR(255) DEFAULT NULL AFTER data_confirmacao;