<div id="dashboardApp">
    <div class="dashboard-container">
        <nav class="navbar">
            <div class="nav-content">
                <h1>WeLinked Dashboard</h1>
                <div class="nav-user">
                    <span>Welcome, {{ user.username }}!</span>
                    <a href="/login/logout" class="btn-logout">Logout</a>
                </div>
            </div>
        </nav>

        <div class="dashboard-content">
            <h2>User Profile</h2>
            <div class="profile-card">
                <div class="profile-item">
                    <strong>Username:</strong> {{user.username }}
                </div>
                <div class="profile-item">
                    <strong>Full name:</strong> {{ user.full_name }}
                </div>
                <div class="profile-item">
                    <strong>Email:</strong> {{ user.email }}
                </div>
                <div class="profile-item">
                    <strong>Member since:</strong> {{ formatDate(user.created_at || user.created || user.createdAt) }}
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.dashboard-container {
    min-height: 100vh;
    background: #fafbfc;
}

.navbar {
    background: #ffffff;
    color: #2d3748;
    padding: 20px 0;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    border-bottom: 1px solid #e2e8f0;
}

.nav-content {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.nav-content h1 {
    font-size: 24px;
    color: #1a202c;
    font-weight: 600;
}

.nav-user {
    display: flex;
    align-items: center;
    gap: 20px;
}

.nav-user span {
    font-size: 14px;
    color: #4a5568;
}

.btn-logout {
    background: #f7fafc;
    color: #4a5568;
    padding: 8px 16px;
    border-radius: 6px;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.2s;
    border: 1px solid #e2e8f0;
}

.btn-logout:hover {
    background: #edf2f7;
    border-color: #cbd5e0;
    color: #2d3748;
}

.dashboard-content {
    max-width: 1200px;
    margin: 40px auto;
    padding: 0 20px;
}

.dashboard-content h2 {
    color: #2d3748;
    margin-bottom: 24px;
    font-size: 28px;
    font-weight: 600;
}

.profile-card {
    background: #ffffff;
    border-radius: 12px;
    padding: 32px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    border: 1px solid #e2e8f0;
}

.profile-item {
    padding: 16px 0;
    border-bottom: 1px solid #f7fafc;
    font-size: 16px;
    color: #4a5568;
}

.profile-item:last-child {
    border-bottom: none;
}

.profile-item strong {
    color: #2d3748;
    margin-right: 12px;
    font-weight: 600;
}
</style>

<script>
const { createApp } = Vue;

createApp({
    data() {
        return {
            user: <?= json_encode($user) ?>
        }
    },
    methods: {
        formatDate(dateString) {
            if (!dateString) return 'N/A';
            const date = new Date(dateString);
            return date.toLocaleDateString('en-US', { 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            });
        }
    },
    mounted() {
        console.log('Dashboard loaded for user:', this.user.username);
    }
}).mount('#dashboardApp');
</script>
