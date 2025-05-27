/**
 * Manejador centralizado de errores para el panel de administración
 */
const ErrorHandler = {
    /**
     * Tipos de error conocidos
     */
    errorTypes: {
        API_ERROR: 'api_error',
        VALIDATION_ERROR: 'validation_error',
        PERMISSION_ERROR: 'permission_error',
        NETWORK_ERROR: 'network_error'
    },

    /**
     * Traduce los errores a mensajes amigables
     */
    messages: {
        api_error: 'Error en la respuesta del servidor',
        validation_error: 'Error de validación',
        permission_error: 'Error de permisos',
        network_error: 'Error de conexión',
        default: 'Ha ocurrido un error inesperado'
    },

    /**
     * Procesa un error y retorna un objeto con la información necesaria
     */
    handleError(error, context = '') {
        console.error(`[Flowtitude Error][${context}]:`, error);

        // Determinar el tipo de error
        let type = this.errorTypes.API_ERROR;
        let message = this.messages.default;

        if (error.name === 'TypeError' && error.message.includes('fetch')) {
            type = this.errorTypes.NETWORK_ERROR;
            message = this.messages.network_error;
        } else if (error.status === 403) {
            type = this.errorTypes.PERMISSION_ERROR;
            message = this.messages.permission_error;
        } else if (error.status === 400) {
            type = this.errorTypes.VALIDATION_ERROR;
            message = error.message || this.messages.validation_error;
        }

        // Si el error tiene un mensaje específico, usarlo
        if (error.message && !message.includes('Error')) {
            message = error.message;
        }

        return {
            type,
            message,
            context,
            timestamp: new Date().toISOString(),
            details: error.stack || error.toString()
        };
    },

    /**
     * Registra el error en el log del navegador con formato mejorado
     */
    logError(error, context = '') {
        const errorInfo = this.handleError(error, context);
        
        console.group(`🔴 Flowtitude Error: ${errorInfo.type}`);
        console.log('Mensaje:', errorInfo.message);
        console.log('Contexto:', errorInfo.context);
        console.log('Timestamp:', errorInfo.timestamp);
        console.log('Detalles:', errorInfo.details);
        console.groupEnd();

        return errorInfo;
    }
};

// Exportar para uso global
window.FlowtitudeErrorHandler = ErrorHandler; 