

// Initialize dashboard
// function initializeDashboard() {
//     console.log('Initializing dashboard...');
    
//     // Initial data fetch
//     if (dashboardState.timeoutId) {
//         clearTimeout(dashboardState.timeoutId);
//     }
//     dashboardState.timeoutId = setTimeout(() => {
//         fetchDashboardData();
//     }, 0);
    
//     // Setup real-time subscription
//     setupRealtimeSubscription();
// }

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    

    // initializeDashboard();
    // fetchDashboardData();
    // setupRealtimeSubscription();
});

// Cleanup on page unload
// window.addEventListener('beforeunload', function() {
//     if (dashboardState.subscription) {
//         supabaseClient.removeChannel(dashboardState.subscription);
//     }
//     if (dashboardState.timeoutId) {
//         clearTimeout(dashboardState.timeoutId);
//     }
// });

// END
