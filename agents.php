<?php
define('BASE_PATH', $_SERVER['DOCUMENT_ROOT'] . '/omnes-immobilier/');
require_once BASE_PATH . 'config/init.php';

// Inclure les classes nécessaires
require_once BASE_PATH . 'classes/Agent.php';

// Initialiser la classe Agent
$agent = new Agent();

// Récupérer tous les agents
$agents = $agent->getAllAgents();

// Filtrer par spécialité si demandé
$specialite = isset($_GET['specialite']) ? $_GET['specialite'] : null;
if ($specialite) {
    $agents = $agent->getAgentsBySpeciality($specialite);
}

// Récupérer toutes les spécialités pour le filtre
$specialites = [];
$all_agents = $agent->getAllAgents();
foreach ($all_agents as $a) {
    if (!empty($a->specialite) && !in_array($a->specialite, $specialites)) {
        $specialites[] = $a->specialite;
    }
}
sort($specialites);

$page_title = "Nos agents immobiliers";
include BASE_PATH . 'includes/header.php';
?>

<div class="container mt-4">
    <h1 class="section-title mb-4" data-aos="fade-up">Nos agents immobiliers</h1>
    
    <!-- Filtres par spécialité -->
    <?php if (!empty($specialites)) : ?>
    <div class="mb-4" data-aos="fade-up">
        <div class="card">
            <div class="card-body">
                <h5 class="mb-3">Filtrer par spécialité</h5>
                <div class="d-flex flex-wrap gap-2">
                    <a href="/omnes-immobilier/agents.php" class="btn <?php echo !$specialite ? 'btn-primary' : 'btn-outline-primary'; ?>">
                        Tous
                    </a>
                    <?php foreach ($specialites as $spec) : ?>
                        <a href="/omnes-immobilier/agents.php?specialite=<?php echo urlencode($spec); ?>" class="btn <?php echo $specialite === $spec ? 'btn-primary' : 'btn-outline-primary'; ?>">
                            <?php echo htmlspecialchars($spec); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- Liste des agents -->
    <div class="row">
        <?php if (!empty($agents)) : ?>
            <?php foreach ($agents as $index => $agent_item) : ?>
                <div class="col-md-4 mb-4" data-aos="fade-up" data-aos-delay="<?php echo $index % 3 * 100; ?>">
                    <div class="card h-100 agent-card">
                        <div class="card-body text-center">
                            <?php if (isset($agent_item->photo_path) && $agent_item->photo_path) : ?>
                                <img src="<?php echo $agent_item->photo_path; ?>" class="agent-img" alt="<?php echo $agent_item->prenom . ' ' . $agent_item->nom; ?>" onerror="this.src='/omnes-immobilier/assets/img/agents/default.jpg'">
                            <?php else : ?>
                                <img src="/omnes-immobilier/assets/img/agents/default.jpg" class="agent-img" alt="<?php echo $agent_item->prenom . ' ' . $agent_item->nom; ?>" onerror="this.src='https://via.placeholder.com/150?text=Agent'">
                            <?php endif; ?>
                            
                            <h4 class="agent-name mt-3"><?php echo $agent_item->prenom . ' ' . $agent_item->nom; ?></h4>
                            <p class="agent-title"><?php echo htmlspecialchars($agent_item->specialite); ?></p>
                            
                            <div class="agent-contact mb-4">
                                <p><i class="fas fa-phone me-2"></i><?php echo htmlspecialchars($agent_item->telephone); ?></p>
                                <p><i class="fas fa-envelope me-2"></i><?php echo htmlspecialchars($agent_item->email); ?></p>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <a href="/omnes-immobilier/agent-profile.php?id=<?php echo $agent_item->id_agent; ?>" class="btn btn-outline-primary">Voir profil</a>
                                <a href="/omnes-immobilier/appointment.php?agent=<?php echo $agent_item->id_agent; ?>" class="btn btn-primary">Prendre rendez-vous</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else : ?>
            <div class="col-12">
                <div class="alert alert-info">
                    Aucun agent disponible pour le moment.
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include BASE_PATH . 'includes/footer.php'; ?>