
const LOG_ROUTES = {
    logs: `${BruteFortData?.restUrl}logs`,
    log_details: `${BruteFortData?.restUrl}log-details`,
    unlock: (id: number) => `${BruteFortData?.restUrl}logs/${id}/unlock`,
};

export default {LOG_ROUTES}