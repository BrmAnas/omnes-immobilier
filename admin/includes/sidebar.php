<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
    <div class="position-sticky pt-3 sidebar-sticky">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" href="/omnes-immobilier/admin/index.php">
                    <i class="fas fa-tachometer-alt me-2"></i>
                    Tableau de bord
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'properties.php' ? 'active' : ''; ?>" href="/omnes-immobilier/admin/properties.php">
                    <i class="fas fa-home me-2"></i>
                    Propriétés
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'agents.php' ? 'active' : ''; ?>" href="/omnes-immobilier/admin/agents.php">
                    <i class="fas fa-user-tie me-2"></i>
                    Agents
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'clients.php' ? 'active' : ''; ?>" href="/omnes-immobilier/admin/clients.php">
                    <i class="fas fa-users me-2"></i>
                    Clients
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'appointments.php' ? 'active' : ''; ?>" href="/omnes-immobilier/admin/appointments.php">
                    <i class="fas fa-calendar-alt me-2"></i>
                    Rendez-vous
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'events.php' ? 'active' : ''; ?>" href="/omnes-immobilier/admin/events.php">
                    <i class="fas fa-bullhorn me-2"></i>
                    Événements
                </a>
            </li>
        </ul>
        
        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted text-uppercase">
            <span>Paramètres</span>
        </h6>
        <ul class="nav flex-column mb-2">
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'settings.php' ? 'active' : ''; ?>" href="/omnes-immobilier/admin/settings.php">
                    <i class="fas fa-cog me-2"></i>
                    Paramètres
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="/omnes-immobilier/logout.php">
                    <i class="fas fa-sign-out-alt me-2"></i>
                    Déconnexion
                </a>
            </li>
        </ul>
    </div>
</nav>