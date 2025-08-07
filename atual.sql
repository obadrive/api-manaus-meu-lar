-- Ativar extensões necessárias para UUID e geolocalização
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS "postgis";

-- Função e Trigger para atualizar updated_at automaticamente
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Tabela de Usuários
CREATE TABLE usuarios (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL, -- Senha criptografada
    role VARCHAR(50) NOT NULL CHECK (role IN ('morador', 'comerciante', 'turista', 'admin')),
    bairro_id UUID,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP,
    FOREIGN KEY (bairro_id) REFERENCES bairros(id)
);

CREATE TRIGGER trigger_update_updated_at_usuarios
BEFORE UPDATE ON usuarios
FOR EACH ROW
EXECUTE FUNCTION update_updated_at_column();

-- Tabela de Bairros
CREATE TABLE bairros (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    nome VARCHAR(255) NOT NULL,
    geometria GEOMETRY(Polygon, 4326), -- Polígono do bairro
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

CREATE TRIGGER trigger_update_updated_at_bairros
BEFORE UPDATE ON bairros
FOR EACH ROW
EXECUTE FUNCTION update_updated_at_column();

-- Tabela de Categorias (Centralizada)
CREATE TABLE categorias (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    nome VARCHAR(100) NOT NULL,
    tipo VARCHAR(50) NOT NULL, -- e.g., 'mapa', 'evento', 'anuncio'
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

CREATE TRIGGER trigger_update_updated_at_categorias
BEFORE UPDATE ON categorias
FOR EACH ROW
EXECUTE FUNCTION update_updated_at_column();

-- Tabela de Pontos de Interesse (Módulo Mapa)
CREATE TABLE pontos_interesse (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    nome VARCHAR(255) NOT NULL,
    descricao TEXT,
    categoria_id UUID REFERENCES categorias(id),
    localizacao GEOMETRY(Point, 4326), -- Coordenadas geográficas
    bairro_id UUID REFERENCES bairros(id),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

CREATE TRIGGER trigger_update_updated_at_pontos_interesse
BEFORE UPDATE ON pontos_interesse
FOR EACH ROW
EXECUTE FUNCTION update_updated_at_column();

-- Tabela de Eventos (Módulo Eventos)
CREATE TABLE eventos (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    titulo VARCHAR(255) NOT NULL,
    descricao TEXT,
    data_inicio TIMESTAMP NOT NULL,
    data_fim TIMESTAMP,
    localizacao GEOMETRY(Point, 4326),
    bairro_id UUID REFERENCES bairros(id),
    organizador_id UUID REFERENCES usuarios(id),
    orgao_id UUID REFERENCES orgaos_prefeitura(id),
    status VARCHAR(50) DEFAULT 'pendente',
    limite_inscritos INTEGER, -- Limite de participação
    tipo VARCHAR(50), -- 'oficial' ou 'comunitario'
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

CREATE TRIGGER trigger_update_updated_at_eventos
BEFORE UPDATE ON eventos
FOR EACH ROW
EXECUTE FUNCTION update_updated_at_column();

-- Tabela de Inscrições em Eventos
CREATE TABLE inscricoes_eventos (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    evento_id UUID REFERENCES eventos(id),
    usuario_id UUID REFERENCES usuarios(id),
    data_inscricao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

CREATE TRIGGER trigger_update_updated_at_inscricoes_eventos
BEFORE UPDATE ON inscricoes_eventos
FOR EACH ROW
EXECUTE FUNCTION update_updated_at_column();

-- Tabela de Anúncios (Módulo Meu Bairro)
CREATE TABLE anuncios (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    titulo VARCHAR(255) NOT NULL,
    descricao TEXT,
    preco NUMERIC(10, 2),
    categoria_id UUID REFERENCES categorias(id),
    usuario_id UUID REFERENCES usuarios(id),
    bairro_id UUID REFERENCES bairros(id),
    localizacao GEOMETRY(Point, 4326),
    orgao_id UUID REFERENCES orgaos_prefeitura(id),
    status VARCHAR(50) DEFAULT 'pendente',
    validade TIMESTAMP, -- Data de expiração do anúncio
    visualizacoes INTEGER DEFAULT 0, -- Contagem de visualizações
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

CREATE TRIGGER trigger_update_updated_at_anuncios
BEFORE UPDATE ON anuncios
FOR EACH ROW
EXECUTE FUNCTION update_updated_at_column();

-- Tabela de Contatos para Anúncios
CREATE TABLE contatos_anuncios (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    anuncio_id UUID REFERENCES anuncios(id),
    tipo VARCHAR(50) NOT NULL, -- e.g., 'telefone', 'email', 'whatsapp'
    valor VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

CREATE TRIGGER trigger_update_updated_at_contatos_anuncios
BEFORE UPDATE ON contatos_anuncios
FOR EACH ROW
EXECUTE FUNCTION update_updated_at_column();

-- Tabela de Avaliações e Comentários para Anúncios
CREATE TABLE avaliacoes_anuncios (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    anuncio_id UUID REFERENCES anuncios(id),
    usuario_id UUID REFERENCES usuarios(id),
    nota INTEGER CHECK (nota >= 1 AND nota <= 5),
    comentario TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

CREATE TRIGGER trigger_update_updated_at_avaliacoes_anuncios
BEFORE UPDATE ON avaliacoes_anuncios
FOR EACH ROW
EXECUTE FUNCTION update_updated_at_column();

-- Tabela de Favoritos
CREATE TABLE favoritos (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    usuario_id UUID REFERENCES usuarios(id),
    anuncio_id UUID REFERENCES anuncios(id),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

CREATE TRIGGER trigger_update_updated_at_favoritos
BEFORE UPDATE ON favoritos
FOR EACH ROW
EXECUTE FUNCTION update_updated_at_column();

-- Tabela de Campanhas da Prefeitura
CREATE TABLE campanhas_prefeitura (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    titulo VARCHAR(255) NOT NULL,
    descricao TEXT,
    imagem_url VARCHAR(255),
    data_inicio TIMESTAMP,
    data_fim TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

CREATE TRIGGER trigger_update_updated_at_campanhas_prefeitura
BEFORE UPDATE ON campanhas_prefeitura
FOR EACH ROW
EXECUTE FUNCTION update_updated_at_column();

-- Tabela de Postagens (Módulo Feed)
CREATE TABLE postagens (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    usuario_id UUID REFERENCES usuarios(id),
    conteudo TEXT NOT NULL,
    tipo VARCHAR(50) NOT NULL CHECK (tipo IN ('geral', 'comunicado_oficial', 'pergunta', 'recomendacao')),
    bairro_id UUID REFERENCES bairros(id),
    localizacao GEOMETRY(Point, 4326),
    fixado BOOLEAN DEFAULT FALSE, -- Indica se está fixado no topo
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

CREATE TRIGGER trigger_update_updated_at_postagens
BEFORE UPDATE ON postagens
FOR EACH ROW
EXECUTE FUNCTION update_updated_at_column();

-- Tabela de Interações com Postagens
CREATE TABLE interacoes_postagens (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    postagem_id UUID REFERENCES postagens(id),
    usuario_id UUID REFERENCES usuarios(id),
    tipo VARCHAR(50) NOT NULL CHECK (tipo IN ('curtida', 'comentario')),
    comentario TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

CREATE TRIGGER trigger_update_updated_at_interacoes_postagens
BEFORE UPDATE ON interacoes_postagens
FOR EACH ROW
EXECUTE FUNCTION update_updated_at_column();

-- Tabela de Serviços (Módulo Serviços)
CREATE TABLE servicos (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    nome VARCHAR(255) NOT NULL,
    descricao TEXT,
    setor VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

CREATE TRIGGER trigger_update_updated_at_servicos
BEFORE UPDATE ON servicos
FOR EACH ROW
EXECUTE FUNCTION update_updated_at_column();

-- Tabela de Protocolos (Módulo Serviços)
CREATE TABLE protocolos (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    servico_id UUID REFERENCES servicos(id),
    usuario_id UUID REFERENCES usuarios(id),
    status VARCHAR(50) NOT NULL DEFAULT 'aberto',
    data_solicitacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_conclusao TIMESTAMP,
    responsavel_id UUID REFERENCES usuarios(id), -- Responsável pelo protocolo
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

CREATE TRIGGER trigger_update_updated_at_protocolos
BEFORE UPDATE ON protocolos
FOR EACH ROW
EXECUTE FUNCTION update_updated_at_column();

-- Tabela de Notificações (Módulo Extras)
CREATE TABLE notificacoes (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    usuario_id UUID REFERENCES usuarios(id),
    mensagem TEXT NOT NULL,
    lida BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

CREATE TRIGGER trigger_update_updated_at_notificacoes
BEFORE UPDATE ON notificacoes
FOR EACH ROW
EXECUTE FUNCTION update_updated_at_column();

-- Tabela de Conquistas (Módulo Gamificação)
CREATE TABLE conquistas (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    nome VARCHAR(255) NOT NULL,
    descricao TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

CREATE TRIGGER trigger_update_updated_at_conquistas
BEFORE UPDATE ON conquistas
FOR EACH ROW
EXECUTE FUNCTION update_updated_at_column();

-- Tabela de Relacionamento Usuários-Conquistas
CREATE TABLE usuarios_conquistas (
    usuario_id UUID REFERENCES usuarios(id),
    conquista_id UUID REFERENCES conquistas(id),
    data_obtencao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP,
    PRIMARY KEY (usuario_id, conquista_id)
);

CREATE TRIGGER trigger_update_updated_at_usuarios_conquistas
BEFORE UPDATE ON usuarios_conquistas
FOR EACH ROW
EXECUTE FUNCTION update_updated_at_column();

-- Tabela de Missões (Módulo Gamificação)
CREATE TABLE missoes (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    nome VARCHAR(255) NOT NULL,
    descricao TEXT,
    recompensa INTEGER NOT NULL, -- GoCoins
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

CREATE TRIGGER trigger_update_updated_at_missoes
BEFORE UPDATE ON missoes
FOR EACH ROW
EXECUTE FUNCTION update_updated_at_column();

-- Tabela de Relacionamento Usuários-Missões
CREATE TABLE usuarios_missoes (
    usuario_id UUID REFERENCES usuarios(id),
    missao_id UUID REFERENCES missoes(id),
    completada BOOLEAN DEFAULT FALSE,
    data_completacao TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP,
    PRIMARY KEY (usuario_id, missao_id)
);

CREATE TRIGGER trigger_update_updated_at_usuarios_missoes
BEFORE UPDATE ON usuarios_missoes
FOR EACH ROW
EXECUTE FUNCTION update_updated_at_column();

-- Tabela de Pontuação de Usuários (Módulo Gamificação)
CREATE TABLE pontuacao_usuarios (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    usuario_id UUID REFERENCES usuarios(id),
    xp INTEGER DEFAULT 0,
    go_coins INTEGER DEFAULT 0,
    nivel INTEGER DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

CREATE TRIGGER trigger_update_updated_at_pontuacao_usuarios
BEFORE UPDATE ON pontuacao_usuarios
FOR EACH ROW
EXECUTE FUNCTION update_updated_at_column();

-- Tabela de Mídia (Gestão de Imagens e Vídeos)
CREATE TABLE midia (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    url VARCHAR(255) NOT NULL,
    tipo VARCHAR(50) NOT NULL, -- e.g., 'imagem', 'video'
    entidade_id UUID NOT NULL,
    entidade_tipo VARCHAR(50) NOT NULL, -- e.g., 'evento', 'anuncio', 'postagem'
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

CREATE TRIGGER trigger_update_updated_at_midia
BEFORE UPDATE ON midia
FOR EACH ROW
EXECUTE FUNCTION update_updated_at_column();

-- Tabela de Comerciantes (Detalhes Adicionais)
CREATE TABLE comerciantes (
    usuario_id UUID PRIMARY KEY REFERENCES usuarios(id),
    cnpj VARCHAR(20),
    razao_social VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

CREATE TRIGGER trigger_update_updated_at_comerciantes
BEFORE UPDATE ON comerciantes
FOR EACH ROW
EXECUTE FUNCTION update_updated_at_column();

-- Tabela de Órgãos da Prefeitura
CREATE TABLE orgaos_prefeitura (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    nome VARCHAR(255) NOT NULL,
    descricao TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

CREATE TRIGGER trigger_update_updated_at_orgaos_prefeitura
BEFORE UPDATE ON orgaos_prefeitura
FOR EACH ROW
EXECUTE FUNCTION update_updated_at_column();

-- Tabela de Recompensas (Loja de Recompensas)
CREATE TABLE recompensas (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    nome VARCHAR(255) NOT NULL,
    descricao TEXT,
    custo_go_coins INTEGER NOT NULL,
    quantidade_disponivel INTEGER,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

CREATE TRIGGER trigger_update_updated_at_recompensas
BEFORE UPDATE ON recompensas
FOR EACH ROW
EXECUTE FUNCTION update_updated_at_column();

-- Tabela de Compras de Recompensas
CREATE TABLE compras_recompensas (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    usuario_id UUID REFERENCES usuarios(id),
    recompensa_id UUID REFERENCES recompensas(id),
    data_compra TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

CREATE TRIGGER trigger_update_updated_at_compras_recompensas
BEFORE UPDATE ON compras_recompensas
FOR EACH ROW
EXECUTE FUNCTION update_updated_at_column();

-- Tabela de Transações de GoCoins
CREATE TABLE transacoes_go_coins (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    usuario_id UUID REFERENCES usuarios(id),
    valor INTEGER NOT NULL, -- Positivo para ganho, negativo para gasto
    descricao VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

CREATE TRIGGER trigger_update_updated_at_transacoes_go_coins
BEFORE UPDATE ON transacoes_go_coins
FOR EACH ROW
EXECUTE FUNCTION update_updated_at_column();

-- Tabela de Documentos Anexados (Módulo Serviços)
CREATE TABLE documentos_protocolos (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    protocolo_id UUID REFERENCES protocolos(id),
    url VARCHAR(255) NOT NULL,
    tipo VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

CREATE TRIGGER trigger_update_updated_at_documentos_protocolos
BEFORE UPDATE ON documentos_protocolos
FOR EACH ROW
EXECUTE FUNCTION update_updated_at_column();

-- Tabela de Traduções (Internacionalização)
CREATE TABLE traducoes (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    entidade_id UUID NOT NULL,
    entidade_tipo VARCHAR(50) NOT NULL, -- e.g., 'servico', 'evento'
    campo VARCHAR(50) NOT NULL, -- e.g., 'nome', 'descricao'
    lingua VARCHAR(10) NOT NULL, -- e.g., 'pt', 'en'
    valor TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

CREATE TRIGGER trigger_update_updated_at_traducoes
BEFORE UPDATE ON traducoes
FOR EACH ROW
EXECUTE FUNCTION update_updated_at_column();

-- Tabela de Logs de Alteração
CREATE TABLE logs_acao (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    usuario_id UUID REFERENCES usuarios(id),
    acao VARCHAR(50) NOT NULL, -- e.g., 'criacao', 'edicao', 'exclusao'
    entidade_id UUID,
    entidade_tipo VARCHAR(50), -- e.g., 'anuncio', 'evento'
    detalhes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabela de Configurações de Usuário
CREATE TABLE configuracoes_usuario (
    usuario_id UUID PRIMARY KEY REFERENCES usuarios(id),
    idioma VARCHAR(10) DEFAULT 'pt',
    tema VARCHAR(50) DEFAULT 'claro',
    notificacoes_ativadas BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

CREATE TRIGGER trigger_update_updated_at_configuracoes_usuario
BEFORE UPDATE ON configuracoes_usuario
FOR EACH ROW
EXECUTE FUNCTION update_updated_at_column();

-- Tabela de Integração com gov.br
CREATE TABLE integracao_govbr (
    usuario_id UUID PRIMARY KEY REFERENCES usuarios(id),
    token_acesso VARCHAR(255),
    data_expiracao TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

CREATE TRIGGER trigger_update_updated_at_integracao_govbr
BEFORE UPDATE ON integracao_govbr
FOR EACH ROW
EXECUTE FUNCTION update_updated_at_column();

-- Tabela de Mapas Offline
CREATE TABLE mapas_offline (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    usuario_id UUID REFERENCES usuarios(id),
    regiao VARCHAR(255),
    data_download TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

CREATE TRIGGER trigger_update_updated_at_mapas_offline
BEFORE UPDATE ON mapas_offline
FOR EACH ROW
EXECUTE FUNCTION update_updated_at_column();

-- Índices para otimização de consultas
CREATE INDEX idx_usuarios_role ON usuarios(role);
CREATE INDEX idx_pontos_interesse_categoria_id ON pontos_interesse(categoria_id);
CREATE INDEX idx_eventos_data_inicio ON eventos(data_inicio);
CREATE INDEX idx_anuncios_categoria_id ON anuncios(categoria_id);
CREATE INDEX idx_postagens_tipo ON postagens(tipo);
CREATE INDEX idx_protocolos_status ON protocolos(status);
CREATE INDEX idx_notificacoes_lida ON notificacoes(lida);

-- Índices geoespaciais para consultas de localização
CREATE INDEX idx_pontos_interesse_localizacao ON pontos_interesse USING GIST (localizacao);
CREATE INDEX idx_eventos_localizacao ON eventos USING GIST (localizacao);
CREATE INDEX idx_anuncios_localizacao ON anuncios USING GIST (localizacao);
CREATE INDEX idx_postagens_localizacao ON postagens USING GIST (localizacao);
CREATE INDEX idx_bairros_geometria ON bairros USING GIST (geometria);

-- Índices para relacionamentos frequentes
CREATE INDEX idx_interacoes_postagens_postagem_id ON interacoes_postagens(postagem_id);
CREATE INDEX idx_usuarios_conquistas_usuario_id ON usuarios_conquistas(usuario_id);
CREATE INDEX idx_usuarios_missoes_usuario_id ON usuarios_missoes(usuario_id);
CREATE INDEX idx_midia_entidade_id ON midia(entidade_id);
CREATE INDEX idx_compras_recompensas_usuario_id ON compras_recompensas(usuario_id);
CREATE INDEX idx_transacoes_go_coins_usuario_id ON transacoes_go_coins(usuario_id);
CREATE INDEX idx_documentos_protocolos_protocolo_id ON documentos_protocolos(protocolo_id);
CREATE INDEX idx_contatos_anuncios_anuncio_id ON contatos_anuncios(anuncio_id);
CREATE INDEX idx_avaliacoes_anuncios_anuncio_id ON avaliacoes_anuncios(anuncio_id);
CREATE INDEX idx_favoritos_usuario_id ON favoritos(usuario_id);
CREATE INDEX idx_logs_acao_usuario_id ON logs_acao(usuario_id);
CREATE INDEX idx_mapas_offline_usuario_id ON mapas_offline(usuario_id);