import React from 'react';

const Dashboard: React.FC = () => {
  return (
    <div className="brutefort-dashboard">
      <h1>BruteFort Dashboard</h1>
      <p>Welcome to your security dashboard. Here you can monitor and manage your site's security.</p>
      
      <div className="brutefort-stats-grid">
        <div className="brutefort-stat-card">
          <div className="brutefort-stat-number">0</div>
          <div className="brutefort-stat-label">Blocked Attempts</div>
        </div>
        
        <div className="brutefort-stat-card">
          <div className="brutefort-stat-number">0</div>
          <div className="brutefort-stat-label">Whitelisted IPs</div>
        </div>
        
        <div className="brutefort-stat-card">
          <div className="brutefort-stat-number">0</div>
          <div className="brutefort-stat-label">Blacklisted IPs</div>
        </div>
        
        <div className="brutefort-stat-card">
          <div className="brutefort-stat-number">0</div>
          <div className="brutefort-stat-label">Security Events</div>
        </div>
      </div>
      
      <div className="brutefort-section">
        <h2>Recent Activity</h2>
        <p>No recent security events to display.</p>
      </div>
    </div>
  );
};

export default Dashboard;
