<?php
require_once __DIR__ . '/translations.php';

$projectsPath = realpath(__DIR__ . '/../');
$configFile = __DIR__ . '/config.json';
$config = file_exists($configFile) ? json_decode(file_get_contents($configFile), true) : ['projects' => [], 'default' => '', 'language' => 'cs'];

$currentLanguage = getCurrentLanguage($config);
$translations = loadTranslations($currentLanguage);
$availableLanguages = getAvailableLanguages();

$projects = $config['projects'] ?? [];
$default = $config['default'] ?? '';

if (!empty($default) && isset($projects[$default])) {
    $entry = $projects[$default]['entry'] ?? '';
    $url = "http://localhost/" . $default . ($entry ? "/$entry/" : "/");
    header("Location: $url");
    exit;
}

$folders = array_filter(scandir($projectsPath), function ($folder) use ($projectsPath) {
    return is_dir($projectsPath . '/' . $folder) && !in_array($folder, ['.', '..', 'dashboard']);
});

$groups = [];
$modificationTimes = [];
foreach ($folders as $folder) {
    $group = $projects[$folder]['group'] ?? 'Ostatní';
    $groups[$group][] = $folder;
    $modificationTimes[$folder] = filemtime($projectsPath . '/' . $folder);
}
ksort($groups);
?>
<!DOCTYPE html>
<html lang="cs">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Localhost Dashboard</title>
    <link rel="icon" type="image/svg+xml" href="./assets/icon.svg">
    <!-- Tabler project & Icons on Head-->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/core@latest/dist/css/tabler.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/tabler-icons.min.css">
    <style>
        html {
            margin: 0 !important;
        }

        .search-input {
            max-width: 400px;
            margin-bottom: 1rem;
        }

        .badge {
            margin: 0.15rem;
        }

        .project-card {
            transition: box-shadow 0.2s;
        }

        .project-card:hover {
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.07);
        }

        .card-title a {
            color: inherit;
            text-decoration: none;
        }

        .card-title a:hover {
            text-decoration: underline;
        }

        .nav-pills .nav-link.active {
            background-color: #206bc4;
        }

        body {
            height: auto;
        }

        .dropdown-menu  {
            top: 100%
        }
    </style>
</head>

<body lang="cs" data-bs-theme="light">
    <header class="navbar navbar-expand-md d-print-none sticky-top shadow-sm">
        <div class="container-xl">
            <h1 class="navbar-brand navbar-brand-autodark d-none-navbar-horizontal pe-0 pe-md-3">
                <a href="#" class="d-flex align-items-center gap-2 text-decoration-none">
                    <img src="./assets/icon.svg" width="24" alt=""> 
                    <span>Localhost Dashboard</span>
                </a>
            </h1>
            <form class="d-none d-md-block mx-auto w-100" style="max-width: 500px;" role="search" onsubmit="event.preventDefault();">
                <div class="input-icon">
                    <span class="input-icon-addon">
                    <i class="ti ti-search icon"></i>
                    </span>
                    <input type="search" class="form-control" id="search" placeholder="<?= htmlspecialchars(t($translations, 'app.search_placeholder')) ?>" aria-label="Search">
                </div>
            </form>

            <div class="navbar-nav flex-row order-md-last ms-auto">
                <div class="nav-item dropdown position-relative">
                    <a class="nav-link px-2" href="#" id="languageDropdown" onclick="toggleLanguageDropdown(event)" title="<?= htmlspecialchars(t($translations, 'language.select')) ?>">
                        <i class="ti ti-language icon me-1"></i>
                        <span class="d-none d-sm-inline"><?= strtoupper($currentLanguage) ?></span>
                        <i class="ti ti-chevron-down ms-1"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" id="languageDropdownMenu" style="display: none;">
                        <?php foreach ($availableLanguages as $lang): ?>
                            <li>
                                <a class="dropdown-item <?= $lang === $currentLanguage ? 'active' : '' ?>" 
                                   href="#" onclick="event.preventDefault(); changeLanguage('<?= $lang ?>')">
                                    <?= htmlspecialchars(t($translations, "language." . ($lang === 'cs' ? 'czech' : 'english'))) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <div class="nav-item">
                    <a href="#" class="nav-link px-0  hide-theme-dark" id="setDark" data-bs-toggle="tooltip"
                        data-bs-placement="bottom" aria-label="<?= htmlspecialchars(t($translations, 'app.dark_mode')) ?>" data-bs-original-title="<?= htmlspecialchars(t($translations, 'app.dark_mode')) ?>">
                         <i class="ti ti-moon icon icon-1"></i>
                    </a>
                    <a href="#" class="nav-link px-0  hide-theme-light" id="setLight" data-bs-toggle="tooltip"
                        data-bs-placement="bottom" aria-label="<?= htmlspecialchars(t($translations, 'app.light_mode')) ?>" data-bs-original-title="<?= htmlspecialchars(t($translations, 'app.light_mode')) ?>">
                        <i class="ti ti-sun icon icon-1"></i>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <div class="container-xl">
        <div id="flash-container" style="position: fixed; botoom: 1rem; right: 1rem; z-index: 1055; max-width: 400px;">
        </div>
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4 mt-3">
            <div class="btn-list mb-0" id="groupTabs">
                <a href="#" class="btn btn-outline-primary active" data-group="all"><?= htmlspecialchars(t($translations, 'navigation.all')) ?></a>
                <?php foreach (array_keys($groups) as $group): ?>
                    <a href="#" class="btn btn-outline-primary" data-group="<?= htmlspecialchars($group) ?>">
                        <?= htmlspecialchars($group) ?>
                    </a>
                <?php endforeach; ?>
            </div>

            <div class="d-flex align-items-center gap-2">
                <label for="sort" class="form-label mb-0"><?= htmlspecialchars(t($translations, 'navigation.sort_by')) ?></label>
                <select id="sort" class="form-select w-auto">
                    <option value="az"><?= htmlspecialchars(t($translations, 'navigation.sort_az')) ?></option>
                    <option value="za"><?= htmlspecialchars(t($translations, 'navigation.sort_za')) ?></option>
                    <option value="modified"><?= htmlspecialchars(t($translations, 'navigation.sort_modified')) ?></option>
                </select>
            </div>
        </div>
        <div class="row row-cards" id="projects">
            <?php foreach ($groups as $groupName => $folders): ?>
                <?php foreach ($folders as $folder):
                    $meta = $projects[$folder] ?? ['title' => $folder];
                    $title = $meta['title'] ?? $folder;
                    $description = $meta['description'] ?? '';
                    $tags = $meta['tags'] ?? [];
                    $entryPath = isset($meta['entry']) && $meta['entry'] !== ''
                        ? '/' . trim($meta['entry'], '/')
                        : '';
                    $dataModified = $modificationTimes[$folder] ?? 0;
                    ?>
                    <div class="col-sm-6 col-lg-4 project" data-group="<?= htmlspecialchars($groupName) ?>"
                        data-name="<?= strtolower($title) ?>" data-tags="<?= implode(' ', $tags) ?>"
                        data-modified="<?= $dataModified ?>">
                        <div class="card project-card h-100 d-flex flex-column">
                            <div class="card-body">
                                <h2 class="mb-1">
                                    <a href="http://localhost/<?= $folder . $entryPath ?>/" target="_blank"
                                        class="text-decoration-none stretched-link"><?= htmlspecialchars($title) ?></a>
                                </h2>
                                <?php if ($description): ?>
                                    <div class="text-muted mb-2"><?= htmlspecialchars($description) ?></div>
                                <?php endif; ?>

                                <?php if (!empty($tags)): ?>
                                    <div class="mt-2">
                                        <?php foreach ($tags as $tag): ?>
                                            <span class="badge bg-azure-lt me-1"><?= htmlspecialchars($tag) ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="card-footer d-flex justify-content-between align-items-center small text-muted">
                                <div class="d-flex align-items-center">
                                    <i class="ti ti-clock icon me-1"></i>
                                    <?= date('d.m.Y H:i', $dataModified) ?>
                                </div>

                                <a href="#" onclick="event.preventDefault(); editProject('<?= $folder ?>')"
                                    class="btn btn-sm btn-outline-primary position-relative z-1">
                                    <i class="ti ti-edit me-1"></i> <?= htmlspecialchars(t($translations, 'project.edit')) ?>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </div>


        <div class="modal modal-blur fade" id="editModal" tabindex="-1" role="dialog" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <form onsubmit="submitEdit(); return false;">
                        <div class="modal-header">
                            <h5 class="modal-title"><?= htmlspecialchars(t($translations, 'project.edit_title')) ?> <span id="edit-project-name"
                                    class="text-muted"></span></h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                aria-label="<?= htmlspecialchars(t($translations, 'project.close')) ?>"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" id="edit-folder">
                            <div class="mb-3">
                                <label class="form-label"><?= htmlspecialchars(t($translations, 'project.name')) ?></label>
                                <input type="text" class="form-control" id="edit-title" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><?= htmlspecialchars(t($translations, 'project.description')) ?></label>
                                <input type="text" class="form-control" id="edit-description">
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><?= htmlspecialchars(t($translations, 'project.tags')) ?></label>
                                <input type="text" class="form-control" id="edit-tags">
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><?= htmlspecialchars(t($translations, 'project.group')) ?></label>
                                <input type="text" class="form-control" id="edit-group">
                            </div>
                            <div class="mb-3">
                                <label class="form-label"><?= htmlspecialchars(t($translations, 'project.entry')) ?></label>
                                <input type="text" class="form-control" id="edit-entry">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary"><i class="ti ti-device-floppy me-1"></i> <?= htmlspecialchars(t($translations, 'project.save')) ?></button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= htmlspecialchars(t($translations, 'project.cancel')) ?></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <footer class="footer footer-transparent d-print-none mt-5">
        <div class="container-xl">
            <div class="row text-center align-items-center flex-column">
                <div class="col-auto">
                    <small class="text-muted">
                        &copy; <?= date('Y') ?> <?= htmlspecialchars(t($translations, 'footer.copyright')) ?> <a href="https://simkanin.cz"
                            target="_blank" rel="noopener" class="text-reset text-decoration-underline"><?= htmlspecialchars(t($translations, 'footer.author')) ?></a>
                    </small>
                </div>
            </div>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@tabler/core@latest/dist/js/tabler.min.js"></script>
    <script>
        const config = <?= json_encode($projects) ?>;
        const translations = <?= json_encode($translations) ?>;
        const currentLanguage = '<?= $currentLanguage ?>';
    </script>
    <script src="./assets/scripts.js"></script>
</body>

</html>
