#!/bin/bash

# Cores
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

echo -e "${BLUE}====================================================${NC}"
echo -e "${BLUE}   DataTransform Laravel - Setup                     ${NC}"
echo -e "${BLUE}====================================================${NC}"

# 1. Verificar Docker
echo -e "${BLUE}[1/4] Verificando pré-requisitos...${NC}"
if ! [ -x "$(command -v docker)" ]; then
    echo -e "${RED}[!] Erro: Docker não está instalado.${NC}" >&2
    exit 1
fi

if ! docker info > /dev/null 2>&1; then
    echo -e "${RED}[!] Erro: Docker não está rodando. Inicie o Docker Desktop.${NC}" >&2
    exit 1
fi
echo -e "${GREEN}[✔] Docker OK${NC}"

# 2. Limpar containers anteriores
echo -e "${BLUE}[2/4] Limpando ambiente anterior...${NC}"
docker compose down --remove-orphans 2>/dev/null
echo -e "${GREEN}[✔] Ambiente limpo${NC}"

# 3. Build e inicialização
echo -e "${BLUE}[3/4] Construindo e iniciando containers...${NC}"
docker compose up -d --build
echo -e "${GREEN}[✔] Container iniciado${NC}"

# 4. Aguardar inicialização (composer install + migrations + seed)
echo -e "${BLUE}[4/4] Aguardando inicialização (composer install, migrations, seed)...${NC}"
echo -e "${YELLOW}     Isso pode levar alguns minutos na primeira execução.${NC}"

# Aguarda até o servidor estar respondendo
MAX_WAIT=180
ELAPSED=0
while [ $ELAPSED -lt $MAX_WAIT ]; do
    if curl -s -o /dev/null -w "%{http_code}" http://localhost:8000/api/produtos-precos 2>/dev/null | grep -q "200"; then
        break
    fi
    sleep 3
    ELAPSED=$((ELAPSED + 3))
    echo -ne "     Aguardando... (${ELAPSED}s)\r"
done

echo ""

if [ $ELAPSED -ge $MAX_WAIT ]; then
    echo -e "${RED}[!] Timeout aguardando o servidor. Verifique os logs:${NC}"
    echo -e "${YELLOW}    docker logs datatransform_app${NC}"
    exit 1
fi

echo -e "${BLUE}====================================================${NC}"
echo -e "${GREEN}[✔] AMBIENTE CONFIGURADO COM SUCESSO!${NC}"
echo -e "${GREEN}    API disponível em: http://localhost:8000${NC}"
echo -e ""
echo -e "${BLUE}Endpoints:${NC}"
echo -e "  POST http://localhost:8000/api/sincronizar/produtos"
echo -e "  POST http://localhost:8000/api/sincronizar/precos"
echo -e "  GET  http://localhost:8000/api/produtos-precos"
echo -e ""
echo -e "${BLUE}Testes:${NC}"
echo -e "  docker compose exec app php artisan test"
echo -e "${BLUE}====================================================${NC}"