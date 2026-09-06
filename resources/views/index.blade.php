@extends('updater::layout')

@section('content')
<div class="card updater-card">
    <!-- Card Header -->
    <div class="updater-header d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h3 class="mb-1 fw-bold text-white"><i class="fa-solid fa-cloud-arrow-down me-2"></i>{{ $productName }} Version Updater</h3>
            <p class="mb-0 text-white-50 small">Keep your application secure and up-to-date with 1-click updates</p>
        </div>
        <div>
            <span class="version-badge">
                Current Version: <strong id="header-version-text">v{{ $currentVersion }}</strong>
            </span>
        </div>
    </div>

    <!-- Card Body -->
    <div class="card-body p-4 p-md-5">

        <!-- Global Alert Box -->
        <div id="alert-box" class="alert d-none mb-4 animate__animated animate__fadeIn" role="alert"></div>

        <!-- Step 1: System Requirements -->
        <div class="mb-4 pb-4 border-bottom">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div class="section-title mb-0">
                    <i class="fa-solid fa-server"></i>
                    <span>1. System Requirements & Server Audit</span>
                </div>
                <div id="audit-status-badge">
                    <span class="badge bg-light text-muted border px-3 py-2 rounded-pill"><i class="fa-solid fa-circle-notch fa-spin me-1"></i> Auditing...</span>
                </div>
            </div>
            
            <div id="requirements-container">
                <div class="p-4 bg-light rounded-4 text-center border">
                    <div class="spinner-border text-primary spinner-border-sm me-2" role="status"></div>
                    <span class="text-muted fw-semibold">Performing server health check...</span>
                </div>
            </div>
        </div>

        <!-- Step 2: License & Update Check -->
        <div class="mb-4 pb-4 border-bottom">
            <div class="section-title">
                <i class="fa-solid fa-key"></i>
                <span>2. License Verification & Check Updates</span>
            </div>
            <form id="check-update-form" class="row g-3">
                <div class="col-md-8">
                    <label for="purchase_code" class="form-label fw-semibold small text-secondary"> Purchase Code</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white text-muted border-end-0"><i class="fa-solid fa-barcode"></i></span>
                        <input type="text" class="form-control border-start-0 ps-0" id="purchase_code" name="purchase_code" 
                               placeholder="Enter your Purchase Code"
                               value="{{ $purchaseCode }}">
                    </div>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" id="btn-check-update" class="btn btn-bf-primary w-100" disabled>
                        <i class="fa-solid fa-magnifying-glass me-2"></i>Check Updates
                    </button>
                </div>
            </form>
        </div>

        <!-- Step 3: Update Available Box -->
        <div id="update-available-box" class="d-none mb-4 p-4 border border-success border-opacity-25 rounded-4 bg-success bg-opacity-10 animate__animated animate__fadeIn">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3">
                <div>
                    <span class="badge bg-success px-3 py-2 rounded-pill mb-2"><i class="fa-solid fa-circle-check me-1"></i> New Version Available</span>
                    <h3 class="mb-0 fw-extrabold text-dark">Version <span id="target-version-text">v1.1.0</span></h3>
                    <small class="text-muted" id="release-date-text"></small>
                </div>
                <div>
                    <button type="button" id="btn-start-update" class="btn btn-bf-success btn-lg">
                        <i class="fa-solid fa-bolt me-2"></i>Update Now (1-Click)
                    </button>
                </div>
            </div>
            <div id="changelog-container" class="mt-3 p-3 bg-white border rounded-3">
                <h6 class="fw-bold mb-2 text-dark"><i class="fa-solid fa-list-ul me-2 text-primary"></i>What's New in this Release:</h6>
                <div id="changelog-text" class="changelog-box p-3"></div>
            </div>
        </div>

        <!-- Step 4: Live Progress Tracker (Ultra Premium Workflow) -->
        <div id="progress-box" class="d-none my-4 p-4 border rounded-4 bg-white shadow-sm animate__animated animate__fadeIn">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-arrows-rotate fa-spin me-2 text-primary"></i>Updating Application...</h5>
                <span class="badge bg-primary px-3 py-2 rounded-pill" id="progress-percent-badge">0%</span>
            </div>
            
            <div class="progress-bar-custom mb-4">
                <div id="update-progress-bar" class="progress-bar bg-primary" role="progressbar" style="width: 0%; transition: width 0.4s ease;"></div>
            </div>

            <ul class="list-group list-group-flush" id="progress-steps-list">
                <li class="list-group-item d-flex align-items-start py-3 bg-transparent border-bottom" id="step-backup">
                    <span class="step-icon step-pending me-3" id="icon-backup"><i class="fa-solid fa-database"></i></span>
                    <div class="flex-grow-1">
                        <div class="fw-bold text-dark d-flex align-items-center justify-content-between">
                            <span>1. Application File & Database Backups</span>
                            <span class="badge bg-secondary rounded-pill" id="badge-step-backup">Pending</span>
                        </div>
                        <div class="text-muted small step-desc mt-1" id="desc-step-backup">
                            <i class="fa-regular fa-clock me-1"></i>Waiting to start backup process...
                        </div>
                    </div>
                </li>
                <li class="list-group-item d-flex align-items-start py-3 bg-transparent border-bottom" id="step-download">
                    <span class="step-icon step-pending me-3" id="icon-download"><i class="fa-solid fa-cloud-arrow-down"></i></span>
                    <div class="flex-grow-1">
                        <div class="fw-bold text-dark d-flex align-items-center justify-content-between">
                            <span>2. Downloading Update Package</span>
                            <span class="badge bg-secondary rounded-pill" id="badge-step-download">Pending</span>
                        </div>
                        <div class="text-muted small step-desc mt-1" id="desc-step-download">
                            <i class="fa-regular fa-clock me-1"></i>Waiting for backup completion...
                        </div>
                    </div>
                </li>
                <li class="list-group-item d-flex align-items-start py-3 bg-transparent" id="step-extract">
                    <span class="step-icon step-pending me-3" id="icon-extract"><i class="fa-solid fa-box-open"></i></span>
                    <div class="flex-grow-1">
                        <div class="fw-bold text-dark d-flex align-items-center justify-content-between">
                            <span>3. Extracting Files & Running Migrations</span>
                            <span class="badge bg-secondary rounded-pill" id="badge-step-extract">Pending</span>
                        </div>
                        <div class="text-muted small step-desc mt-1" id="desc-step-extract">
                            <i class="fa-regular fa-clock me-1"></i>Waiting for package download...
                        </div>
                    </div>
                </li>
            </ul>

            <!-- Retry Container on Error -->
            <div id="retry-container" class="d-none mt-3 text-end">
                <button type="button" id="btn-retry-update" class="btn btn-outline-danger btn-sm rounded-pill px-4">
                    <i class="fa-solid fa-rotate-right me-1"></i>Retry Update
                </button>
            </div>
        </div>

        <!-- Step 5: Update Completion Box -->
        <div id="update-completed-box" class="d-none text-center p-5 border border-success border-opacity-50 rounded-4 bg-success bg-opacity-10 my-4 animate__animated animate__bounceIn">
            <div class="display-3 text-success mb-3"><i class="fa-solid fa-circle-check"></i></div>
            <h3 class="fw-bold text-dark mb-2">Application Successfully Updated!</h3>
            <p class="text-muted mb-4 fs-5">Your application is now running on <span class="badge bg-success fs-6" id="completed-version-badge">v1.1.0</span></p>

            <div class="row justify-content-center g-3 mb-4 text-start">
                <div class="col-md-4">
                    <div class="p-3 bg-white border rounded-3">
                        <div class="text-muted small"><i class="fa-solid fa-file-zipper text-primary me-1"></i> Files Extracted</div>
                        <div class="fw-bold fs-5 text-dark" id="summary-files-count">-</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 bg-white border rounded-3">
                        <div class="text-muted small"><i class="fa-solid fa-database text-success me-1"></i> Database Migrations</div>
                        <div class="fw-bold fs-5 text-dark">Up to Date</div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 bg-white border rounded-3">
                        <div class="text-muted small"><i class="fa-solid fa-broom text-warning me-1"></i> Cache Status</div>
                        <div class="fw-bold fs-5 text-dark">Cleared & Rebuilt</div>
                    </div>
                </div>
            </div>

            <button type="button" class="btn btn-bf-primary btn-lg px-5" onclick="window.location.reload();">
                <i class="fa-solid fa-rotate-right me-2"></i>Refresh Dashboard
            </button>
        </div>

    </div>
</div>

<!-- Beautiful Custom Confirmation Modal -->
<div class="modal fade" id="confirmUpdateModal" tabindex="-1" aria-labelledby="confirmUpdateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content overflow-hidden">
            <div class="modal-header-custom d-flex justify-content-between align-items-center">
                <h5 class="modal-title fw-bold mb-0" id="confirmUpdateModalLabel">
                    <i class="fa-solid fa-shield-halved me-2 text-warning"></i>Confirm Application Update
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="p-3 bg-light rounded-4 mb-4 border">
                    <div class="row align-items-center text-center">
                        <div class="col-5">
                            <div class="text-muted small fw-semibold">Current Installed Version</div>
                            <div class="fs-4 fw-bold text-secondary mt-1"><span class="badge bg-secondary" id="modal-current-ver">v{{ $currentVersion }}</span></div>
                        </div>
                        <div class="col-2 fs-3 text-primary">
                            <i class="fa-solid fa-arrow-right-long"></i>
                        </div>
                        <div class="col-5">
                            <div class="text-muted small fw-semibold">Target Version</div>
                            <div class="fs-4 fw-bold text-success mt-1"><span class="badge bg-success" id="modal-target-ver">v1.1.0</span></div>
                        </div>
                    </div>
                </div>

                <div class="alert alert-warning border-0 bg-warning bg-opacity-10 text-dark rounded-3 mb-3">
                    <i class="fa-solid fa-triangle-exclamation text-warning me-2"></i>
                    <strong>Important:</strong> Automatic file & database backups will be generated and downloaded to your computer. Please do not close or refresh this browser tab during the update process.
                </div>

                <div class="mb-3">
                    <h6 class="fw-bold text-dark mb-2"><i class="fa-solid fa-file-lines me-2 text-primary"></i>What's New:</h6>
                    <div id="modal-changelog-text" class="changelog-box p-3 border rounded-3 bg-white"></div>
                </div>
            </div>
            <div class="modal-footer bg-light border-top-0 p-3">
                <button type="button" class="btn btn-light px-4 rounded-3" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="btn-confirm-start-update" class="btn btn-bf-success px-4 rounded-3">
                    <i class="fa-solid fa-circle-check me-2"></i>Confirm & Start 1-Click Update
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    const csrfToken = $('meta[name="csrf-token"]').attr('content');
    let downloadUrl = '';
    let targetVersion = '';
    let currentVersion = '{{ $currentVersion }}';

    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': csrfToken }
    });

    // 1. Audit Server Requirements
    loadRequirements();

    function loadRequirements() {
        $.ajax({
            url: "{{ route('bugfinder.updater.check-requirements') }}",
            type: 'GET',
            success: function(res) {
                if(res.success) {
                    renderRequirements(res.data);
                    $('#btn-check-update').prop('disabled', false);
                }
            },
            error: function(xhr) {
                $('#btn-check-update').prop('disabled', false);
                $('#requirements-container').html(`
                    <div class="text-danger p-3">
                        <i class="fa-solid fa-triangle-exclamation me-2"></i>
                        Failed to check requirements: ${xhr.responseJSON?.message || 'Server error'}
                    </div>
                `);
            }
        });
    }

    function renderRequirements(data) {
        let allPassed = data.passed;
        let badgeHtml = allPassed 
            ? '<span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2 rounded-pill fw-semibold"><i class="fa-solid fa-circle-check me-1"></i> Server Health: Optimal</span>'
            : '<span class="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25 px-3 py-2 rounded-pill fw-semibold"><i class="fa-solid fa-triangle-exclamation me-1"></i> Server Health: Check Warnings</span>';
        
        $('#audit-status-badge').html(badgeHtml);

        let html = '<div class="row g-3">';

        // 1. PHP Engine Card
        html += `
            <div class="col-md-6">
                <div class="p-3 bg-white border rounded-4 shadow-sm h-100">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="fw-semibold text-dark"><i class="fa-brands fa-php text-primary fs-5 me-2"></i>PHP Engine</span>
                        <span class="badge ${data.php.passed ? 'bg-success' : 'bg-danger'} rounded-pill px-3 py-2">v${data.php.current}</span>
                    </div>
                    <div class="text-muted small">Minimum Required: <strong>PHP v${data.php.required}</strong></div>
                </div>
            </div>
        `;

        // 2. Folder Permissions Card
        let permDetails = '';
        if (data.permissions && data.permissions.details) {
            for (const [folder, details] of Object.entries(data.permissions.details)) {
                permDetails += `<span class="badge ${details.is_writable ? 'bg-success' : 'bg-danger'} rounded-pill px-2 py-1 me-1"><i class="fa-solid ${details.is_writable ? 'fa-check' : 'fa-xmark'} me-1"></i>${folder}</span>`;
            }
        }

        html += `
            <div class="col-md-6">
                <div class="p-3 bg-white border rounded-4 shadow-sm h-100">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="fw-semibold text-dark"><i class="fa-solid fa-folder-tree text-warning fs-5 me-2"></i>Folder Permissions</span>
                        <div>${permDetails}</div>
                    </div>
                    <div class="text-muted small">Storage & Cache write access</div>
                </div>
            </div>
        `;

        // 3. PHP Extensions Grid Card
        html += `
            <div class="col-12">
                <div class="p-3 bg-white border rounded-4 shadow-sm">
                    <div class="fw-semibold text-dark mb-2"><i class="fa-solid fa-cubes text-info fs-5 me-2"></i>Required PHP Extensions</div>
                    <div class="d-flex flex-wrap gap-2">
        `;

        for (const [ext, passed] of Object.entries(data.extensions.details)) {
            html += `
                <div class="border rounded-3 px-3 py-2 bg-light d-flex align-items-center gap-2">
                    <span class="small font-monospace fw-bold text-dark">${ext}</span>
                    <span class="badge ${passed ? 'bg-success' : 'bg-danger'} rounded-circle p-1"><i class="fa-solid ${passed ? 'fa-check' : 'fa-xmark'}"></i></span>
                </div>
            `;
        }

        html += `
                    </div>
                </div>
            </div>
        </div>`;

        $('#requirements-container').html(html);
    }

    // 2. Check for Updates Form Submit
    $('#check-update-form').on('submit', function(e) {
        e.preventDefault();
        const purchaseCode = $('#purchase_code').val();

        $('#btn-check-update').prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin me-2"></i><span>Checking Server...</span>');
        showAlert('info', '<i class="fa-solid fa-cloud-arrow-down me-2"></i>Connecting to BugFinder update server...');

        $.ajax({
            url: "{{ route('bugfinder.updater.check-update') }}",
            type: 'POST',
            data: { purchase_code: purchaseCode },
            success: function(res) {
                $('#btn-check-update').prop('disabled', false).html('<i class="fa-solid fa-magnifying-glass me-2"></i>Check Updates');
                
                if (res.success && res.data.has_update) {
                    downloadUrl = res.data.download_url;
                    targetVersion = res.data.latest_version;

                    $('#target-version-text').text('v' + targetVersion);
                    $('#release-date-text').text(res.data.release_date ? 'Released on: ' + res.data.release_date : '');
                    $('#changelog-text').html(res.data.changelog || 'No detailed changelog provided.');
                    
                    $('#update-available-box').removeClass('d-none');
                    showAlert('success', `🎉 Update Available! Version v${targetVersion} is ready for installation.`);
                } else {
                    $('#update-available-box').addClass('d-none');
                    showAlert('success', res.data.message || '🎉 Your application is already up to date!');
                }
            },
            error: function(xhr) {
                $('#btn-check-update').prop('disabled', false).html('<i class="fa-solid fa-magnifying-glass me-2"></i>Check Updates');
                showAlert('danger', '<i class="fa-solid fa-triangle-exclamation me-2"></i>' + (xhr.responseJSON?.message || 'Error connecting to update server.'));
            }
        });
    });

    // 3. Open Custom Confirmation Modal (Support both IDs)
    $(document).on('click', '#btn-start-update, #btn-open-confirm-modal', function() {
        $('#modal-current-ver').text('v' + currentVersion);
        $('#modal-target-ver').text('v' + targetVersion);
        $('#modal-changelog-text').html($('#changelog-text').html());

        var modalEl = document.getElementById('confirmUpdateModal');
        var modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
        modal.show();
    });

    // 4. Confirm Update from Modal
    $(document).on('click', '#btn-confirm-start-update', function() {
        var modalEl = document.getElementById('confirmUpdateModal');
        var modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) {
            modal.hide();
        }

        $('#update-available-box').addClass('d-none');
        $('#progress-box').removeClass('d-none');
        $('#retry-container').addClass('d-none');
        startUpdateSequence();
    });

    // Retry Button
    $(document).on('click', '#btn-retry-update', function() {
        $('#retry-container').addClass('d-none');
        startUpdateSequence();
    });

    // Update Step Status Helper
    function updateStepStatus(stepKey, status, descText, badgeText) {
        const $icon = $('#icon-' + stepKey);
        const $badge = $('#badge-' + stepKey);
        const $desc = $('#desc-' + stepKey);

        $icon.removeClass('step-pending step-active step-success step-error');
        $icon.addClass('step-' + status);

        if (status === 'active') {
            $icon.html('<i class="fa-solid fa-spinner fa-spin"></i>');
            $badge.removeClass('bg-secondary bg-success bg-danger').addClass('bg-primary').text(badgeText || 'Processing...');
            $desc.html(`<span class="text-primary fw-semibold"><i class="fa-solid fa-spinner fa-spin me-1"></i>${descText}</span>`);
        } else if (status === 'success') {
            $icon.html('<i class="fa-solid fa-check"></i>');
            $badge.removeClass('bg-secondary bg-primary bg-danger').addClass('bg-success').text(badgeText || 'Completed');
            $desc.html(`<span class="text-success fw-semibold"><i class="fa-solid fa-circle-check me-1"></i>${descText}</span>`);
        } else if (status === 'error') {
            $icon.html('<i class="fa-solid fa-xmark"></i>');
            $badge.removeClass('bg-secondary bg-primary bg-success').addClass('bg-danger').text(badgeText || 'Failed');
            $desc.html(`<span class="text-danger fw-semibold"><i class="fa-solid fa-triangle-exclamation me-1"></i>${descText}</span>`);
        } else {
            $icon.html('<i class="fa-regular fa-clock"></i>');
            $badge.removeClass('bg-primary bg-success bg-danger').addClass('bg-secondary').text(badgeText || 'Pending');
            $desc.html(`<i class="fa-regular fa-clock me-1"></i>${descText}`);
        }
    }

    // Update Sequence Execution
    function startUpdateSequence() {
        // Reset UI step statuses
        updateStepStatus('backup', 'active', 'Zipping application files & exporting database tables...', 'Processing...');
        updateStepStatus('download', 'pending', 'Waiting for backup completion...', 'Pending');
        updateStepStatus('extract', 'pending', 'Waiting for package download...', 'Pending');

        updateProgress(15);
        showAlert('info', 'Step 1/3: Generating file & database backups...');

        $.ajax({
            url: "{{ route('bugfinder.updater.create-backup') }}",
            type: 'POST',
            success: function(res) {
                updateProgress(35);
                updateStepStatus('backup', 'success', 'File & DB Backups Generated & Auto-Downloaded!', 'Completed');

                // Render download buttons in step UI
                let downloadHtml = `<div class="mt-2 text-success small fw-semibold">
                    <i class="fa-solid fa-circle-check me-1"></i>Backups Created & Auto-Downloaded!
                    <div class="mt-1">
                        <a href="${res.file_backup_url}" class="btn btn-sm btn-outline-primary py-1 px-2 me-1 small" target="_blank">
                            <i class="fa-solid fa-download me-1"></i>File Backup (${res.file_backup})
                        </a>
                        <a href="${res.db_backup_url}" class="btn btn-sm btn-outline-secondary py-1 px-2 small" target="_blank">
                            <i class="fa-solid fa-database me-1"></i>DB Backup (${res.db_backup})
                        </a>
                    </div>
                </div>`;
                $('#desc-step-backup').html(downloadHtml);

                // Auto Trigger Downloads for BOTH File and DB Backups
                if (res.file_backup_url) {
                    triggerAutoDownload(res.file_backup_url);
                }
                if (res.db_backup_url) {
                    setTimeout(function() {
                        triggerAutoDownload(res.db_backup_url);
                    }, 800); // 800ms delay to prevent browser popup block
                }

                // Proceed to Step 2: Download Package
                stepDownloadPackage();
            },
            error: function(xhr) {
                updateStepStatus('backup', 'error', 'Backup Failed: ' + (xhr.responseJSON?.message || 'Unknown error'), 'Failed');
                $('#retry-container').removeClass('d-none');
                showAlert('danger', 'Backup Failed: ' + (xhr.responseJSON?.message || 'Unknown backup error. Click retry to attempt again.'));
            }
        });
    }

    function stepDownloadPackage() {
        updateStepStatus('download', 'active', `Fetching update package v${targetVersion} from server...`, 'Downloading...');
        updateProgress(55);
        showAlert('info', `Step 2/3: Downloading update package v${targetVersion}...`);

        $.ajax({
            url: "{{ route('bugfinder.updater.download-package') }}",
            type: 'POST',
            data: { download_url: downloadUrl, version: targetVersion },
            success: function(res) {
                updateProgress(75);
                updateStepStatus('download', 'success', `Update package v${targetVersion} downloaded & verified!`, 'Completed');
                
                // Proceed to Step 3: Extract & Migrate
                stepExtractAndMigrate();
            },
            error: function(xhr) {
                updateStepStatus('download', 'error', 'Download Failed: ' + (xhr.responseJSON?.message || 'Download error'), 'Failed');
                $('#retry-container').removeClass('d-none');
                showAlert('danger', 'Download Failed: ' + (xhr.responseJSON?.message || 'Failed to download update package. Click retry to attempt again.'));
            }
        });
    }

    function stepExtractAndMigrate() {
        updateStepStatus('extract', 'active', 'Extracting updated source files, running artisan migrations & clearing cache...', 'Applying Updates...');
        updateProgress(88);
        showAlert('info', 'Step 3/3: Applying code updates & running database migrations...');

        $.ajax({
            url: "{{ route('bugfinder.updater.run-update') }}",
            type: 'POST',
            data: { version: targetVersion },
            success: function(res) {
                updateProgress(100);
                updateStepStatus('extract', 'success', 'Source files extracted, database migrated & application optimized!', 'Completed');

                // Dynamic UI Updates on Success
                currentVersion = targetVersion;
                $('#header-version-text').text('v' + targetVersion);
                $('#completed-version-badge').text('v' + targetVersion);
                
                if (res.extraction && res.extraction.extracted_count) {
                    $('#summary-files-count').text(res.extraction.extracted_count + ' files');
                } else {
                    $('#summary-files-count').text('Updated');
                }

                // Reveal Completion Celebration Card
                setTimeout(function() {
                    $('#progress-box').addClass('d-none');
                    $('#update-completed-box').removeClass('d-none');
                    showAlert('success', `🎉 Application updated to v${targetVersion} successfully!`);
                }, 800);
            },
            error: function(xhr) {
                updateStepStatus('extract', 'error', 'Update Extraction Failed: ' + (xhr.responseJSON?.message || 'Migration error'), 'Failed');
                $('#retry-container').removeClass('d-none');
                showAlert('danger', 'Update Extraction Failed: ' + (xhr.responseJSON?.message || 'Error occurred while applying updates. Click retry to attempt again.'));
            }
        });
    }

    function triggerAutoDownload(url) {
        let iframe = document.createElement('iframe');
        iframe.style.display = 'none';
        iframe.src = url;
        document.body.appendChild(iframe);
        setTimeout(function() {
            $(iframe).remove();
        }, 5000);
    }

    function updateProgress(percent) {
        $('#update-progress-bar').css('width', percent + '%');
        $('#progress-percent-badge').text(percent + '%');
    }

    function showAlert(type, message) {
        $('#alert-box')
            .removeClass('d-none alert-success alert-danger alert-info alert-warning')
            .addClass('alert-' + type)
            .html(message);
    }
});
</script>
@endpush
