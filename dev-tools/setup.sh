#!/bin/bash

# Colores para los mensajes
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

CONFIG_FILE="$(pwd)/dev-tools/config.json"
CURRENT_DIR="$(pwd)"

echo -e "${YELLOW}Configuración inicial de entornos Flowtitude${NC}\n"

# Configurar staging automáticamente como el directorio actual
STAGING_PATH="$CURRENT_DIR"
echo -e "${GREEN}✓ Entorno de desarrollo (staging):${NC} $STAGING_PATH"

# Configurar producción
echo -e "\n${YELLOW}Configuración del entorno de producción:${NC}"
echo -e "Este será el directorio donde se desplegará la versión final del tema para distribución."
read -p "Introduce la ruta completa al directorio de producción (o presiona Enter para saltar): " PRODUCTION_PATH

# Si no se especifica producción, usar un valor por defecto
if [ -z "$PRODUCTION_PATH" ]; then
    PRODUCTION_PATH="/ruta/pendiente/de/configurar"
    echo -e "${YELLOW}⚠️  No se ha especificado ruta de producción${NC}"
else
    echo -e "${GREEN}✓ Ruta de producción configurada${NC}"
fi

# Actualizar config.json
jq --arg staging "$STAGING_PATH" \
   --arg production "$PRODUCTION_PATH" \
   '.development.paths.staging = $staging | .development.paths.production = $production' \
   "$CONFIG_FILE" > "${CONFIG_FILE}.tmp" && mv "${CONFIG_FILE}.tmp" "$CONFIG_FILE"

echo -e "\n${GREEN}✅ Configuración completada${NC}"
echo -e "\nRutas configuradas:"
echo -e "Desarrollo (staging): ${YELLOW}$STAGING_PATH${NC}"
echo -e "Producción: ${YELLOW}$PRODUCTION_PATH${NC}"
echo -e "\nPuedes modificar estas rutas en cualquier momento editando: ${YELLOW}$CONFIG_FILE${NC}" 