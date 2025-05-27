#!/bin/bash

# Colores para los mensajes
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

# Configuración
CONFIG_FILE="$(pwd)/dev-tools/config.json"
SOURCE_DIR="$(pwd)"
BACKUP_DIR="${SOURCE_DIR}/backups"
LOG_FILE="${SOURCE_DIR}/dev-tools/sync.log"
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")

# Verificar que jq está instalado
if ! command -v jq &> /dev/null; then
    echo -e "${RED}❌ Error: jq no está instalado. Por favor, instálalo primero:${NC}"
    echo "brew install jq"
    exit 1
fi

# Verificar que el archivo de configuración existe
if [ ! -f "$CONFIG_FILE" ]; then
    echo -e "${RED}❌ Error: Archivo de configuración no encontrado${NC}"
    echo "Ejecuta primero: ./dev-tools/setup.sh"
    exit 1
fi

# Función para logging
log_message() {
    local level=$1
    local message=$2
    echo -e "${level}${message}${NC}"
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] ${message}" >> "$LOG_FILE"
}

# Crear directorios necesarios
mkdir -p "$BACKUP_DIR"
mkdir -p "${SOURCE_DIR}/dev-tools"
touch "$LOG_FILE"

# Función para crear backup
create_backup() {
    local target_dir=$1
    local backup_name="${THEME_NAME}_${TIMESTAMP}.tar.gz"
    
    log_message "$YELLOW" "📦 Creando backup de $target_dir..."
    tar -czf "${BACKUP_DIR}/${backup_name}" -C "$target_dir" .
    
    if [ $? -eq 0 ]; then
        log_message "$GREEN" "✅ Backup creado: ${backup_name}"
    else
        log_message "$RED" "❌ Error al crear backup"
        exit 1
    fi
}

# Función para sincronizar archivos
sync_files() {
    local target_dir=$1
    
    # Obtener directorios y archivos del config.json
    SYNC_DIRS=($(jq -r '.development.sync.directories[]' "$CONFIG_FILE"))
    SYNC_FILES=($(jq -r '.development.sync.files[]' "$CONFIG_FILE"))
    EXCLUDE=($(jq -r '.development.sync.exclude[]' "$CONFIG_FILE"))
    
    # Crear string de exclusiones para rsync
    EXCLUDE_STRING=""
    for item in "${EXCLUDE[@]}"; do
        EXCLUDE_STRING="$EXCLUDE_STRING --exclude='$item'"
    done
    
    # Sincronizar directorios
    for dir in "${SYNC_DIRS[@]}"; do
        if [ -d "${SOURCE_DIR}/${dir}" ]; then
            log_message "$YELLOW" "🔄 Sincronizando directorio: ${dir}"
            eval "rsync -av --delete $EXCLUDE_STRING \"${SOURCE_DIR}/${dir}/\" \"${target_dir}/${dir}/\""
        fi
    done
    
    # Sincronizar archivos individuales
    for file in "${SYNC_FILES[@]}"; do
        if [ -f "${SOURCE_DIR}/${file}" ]; then
            log_message "$YELLOW" "🔄 Sincronizando archivo: ${file}"
            cp "${SOURCE_DIR}/${file}" "${target_dir}/${file}"
        fi
    done
}

# Función principal
main() {
    local env=$1
    local target_dir
    
    # Verificar argumento
    if [ -z "$env" ]; then
        log_message "$RED" "❌ Debes especificar el entorno (staging o production)"
        echo -e "Uso: $0 [staging|production]"
        exit 1
    fi
    
    # Obtener ruta del entorno
    if [ "$env" = "staging" ]; then
        target_dir=$(jq -r '.development.paths.staging' "$CONFIG_FILE")
    elif [ "$env" = "production" ]; then
        target_dir=$(jq -r '.development.paths.production' "$CONFIG_FILE")
    else
        log_message "$RED" "❌ Entorno inválido. Usa 'staging' o 'production'"
        exit 1
    fi
    
    # Verificar que la ruta existe
    if [ ! -d "$target_dir" ]; then
        log_message "$RED" "❌ El directorio de destino no existe: $target_dir"
        echo -e "Ejecuta ./dev-tools/setup.sh para configurar las rutas"
        exit 1
    fi
    
    log_message "$YELLOW" "🚀 Iniciando sincronización con $env..."
    
    # Crear backup del destino antes de sincronizar
    create_backup "$target_dir"
    
    # Sincronizar archivos
    sync_files "$target_dir"
    
    log_message "$GREEN" "✅ Sincronización completada con $env"
    log_message "$YELLOW" "📝 Revisa el log en: $LOG_FILE"
}

# Ejecutar script
main "$1" 