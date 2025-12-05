<div class="section-header">
    <h2 class="section-title">Input Grades</h2>
    <p class="section-subtitle">Grade students who haven't been evaluated yet</p>
</div>

<div class="grades-filter-container">
    <div class="filter-card">
        <form id="grade-filter-form">
            <div class="filter-row">
                <div class="form-group">
                    <label for="year_level" class="form-label">
                        <i class="fas fa-graduation-cap"></i>
                        Year Level
                    </label>
                    <select name="year_level" id="year_level" class="form-control">
                        <option value="">All Year Levels</option>
                        <option value="1st Year">1st Year</option>
                        <option value="2nd Year">2nd Year</option>
                        <option value="3rd Year">3rd Year</option>
                        <option value="4th Year">4th Year</option>
                        <option value="5th Year">5th Year</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="subject_search" class="form-label">
                        <i class="fas fa-book"></i>
                        Search Subject
                    </label>
                    <input type="text" name="subject_search" id="subject_search" class="form-control" placeholder="Search by subject code or name...">
                </div>
                
                <div class="form-group">
                    <label for="grade_status" class="form-label">
                        <i class="fas fa-filter"></i>
                        Grade Status
                    </label>
                    <select name="grade_status" id="grade_status" class="form-control">
                        <option value="ungraded">Ungraded Only</option>
                        <option value="graded">Graded Only</option>
                        <option value="all">All Subjects</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="sort_by" class="form-label">
                        <i class="fas fa-sort"></i>
                        Sort By
                    </label>
                    <select name="sort_by" id="sort_by" class="form-control">
                        <option value="lastname">Last Name</option>
                        <option value="firstname">First Name</option>
                        <option value="middlename">Middle Name</option>
                        <option value="subject">Subject</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="search" class="form-label">
                        <i class="fas fa-search"></i>
                        Search Student
                    </label>
                    <input type="text" name="search" id="search" class="form-control" placeholder="Search by student name...">
                </div>
            </div>
            
            <div class="filter-actions">
                <button type="submit" class="btn-primary">
                    <i class="fas fa-filter"></i>
                    Apply Filters
                </button>
                <button type="button" id="reset-filters" class="btn-secondary">
                    <i class="fas fa-redo"></i>
                    Reset
                </button>
            </div>
        </form>
    </div>
</div>

<div class="grades-content-container">
    <div class="loading-container" id="loading-container" style="display: none;">
        <div class="loading-spinner"></div>
        <p>Loading students...</p>
    </div>
    
    <div class="no-students-container" id="no-students-container" style="display: none;">
        <div class="no-data-icon">
            <i class="fas fa-user-graduate"></i>
        </div>
        <h3>No Students Found</h3>
        <p>No ungraded students match your current filters.</p>
    </div>
    
    <div class="students-grid" id="students-grid">
        <!-- Students will be loaded here via AJAX -->
    </div>
</div>

<!-- Grade Input Modal -->
<div class="modal-overlay" id="grade-modal" style="display: none;">
    <div class="modal-container horizontal-modal">
        <div class="modal-header">
            <h3 style="color: white;">Input Grade</h3>
            <button class="close-modal" id="close-grade-modal">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <form id="grade-form">
                <input type="hidden" id="grade_student_id" name="student_id">
                <input type="hidden" id="grade_subject_id" name="subject_id">
                
                <div class="modal-grid">
                    <!-- Left Column - Student Info -->
                    <div class="modal-column student-column">
                        <div class="student-info-card">
                            <div class="student-avatar">
                                <img src="https://ui-avatars.com/api/?name=Student+Name&background=4361ee&color=fff" 
                                     alt="Student Avatar" id="student_avatar">
                            </div>
                            <div class="student-details">
                                <h4 id="student_full_name"></h4>
                                <p class="student-id" id="student_id_display"></p>
                                <p class="student-course" id="student_course"></p>
                            </div>
                        </div>
                        
                        <div class="subjects-list-container">
                            <h5>Available Subjects</h5>
                            <div class="subjects-list" id="subjects_list">
                                <!-- Subjects will be populated here -->
                            </div>
                        </div>
                    </div>
                    
                    <!-- Right Column - Grade Input -->
                    <div class="modal-column grade-column">
                        <div class="subject-info-card">
                            <h5>Subject Information</h5>
                            <div class="subject-details-grid">
                                <div class="subject-detail-item">
                                    <span class="label">Subject Code:</span>
                                    <span class="value" id="subject_code">-</span>
                                </div>
                                <div class="subject-detail-item">
                                    <span class="label">Subject Name:</span>
                                    <span class="value" id="subject_name">-</span>
                                </div>
                                <div class="subject-detail-item">
                                    <span class="label">Units:</span>
                                    <span class="value" id="subject_units">-</span>
                                </div>
                                <div class="subject-detail-item">
                                    <span class="label">Year Level:</span>
                                    <span class="value" id="subject_year">-</span>
                                </div>
                                <div class="subject-detail-item">
                                    <span class="label">Semester:</span>
                                    <span class="value" id="subject_semester">-</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="grade-input-card">
                            <label for="grade" class="form-label">
                                <i class="fas fa-pen"></i>
                                Grade
                            </label>
                            <select name="grade" id="grade" class="form-control" required>
                                <option value="">Select Grade</option>
                                <option value="1.00">1.0 - Excellent</option>
                                <option value="1.00">1.1 - Excellent</option>
                                <option value="1.25">1.2 - Very Good</option>
                                <option value="1.00">1.3 - Very Good</option>
                                <option value="1.00">1.4 - Very Good</option>
                                <option value="1.50">1.5 - Good</option>
                                <option value="1.00">1.6 - Good</option>
                                <option value="1.75">1.7 - Satisfactory</option>
                                <option value="1.00">1.8 - Satisfactory</option>
                                <option value="1.00">1.9 - Excellent</option>
                                <option value="2.00">2.0 - Fair</option>
                                <option value="2.00">2.1 - Fair</option>
                                <option value="2.00">2.2 - Pass</option>
                                <option value="2.25">2.3 - Pass</option>
                                <option value="2.00">2.4 - Pass</option>
                                <option value="2.50">2.5 - Conditional</option>
                                <option value="2.00">2.6 - Fair</option>
                                <option value="2.75">2.7 - Conditional</option>
                                <option value="2.00">2.8 - Conditional</option>
                                <option value="2.00">2.9 - Conditional</option>
                                <option value="3.00">3.0 - Conditional</option>
                                <option value="4.00">4.0 - Failed</option>
                                <option value="5.00">5.0 - Failed</option>
                                <option value="INC">INC - Incomplete</option>
                                <option value="DRP">DRP - Dropped</option>
                            </select>
                            <small class="form-text">Select the appropriate grade for the student</small>
                        </div>
                        
                        <div class="form-actions">
                            <button type="button" class="btn-cancel" id="cancel-grade">Cancel</button>
                            <button type="submit" class="btn-primary">
                                <i class="fas fa-save"></i>
                                Save Grade
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.grades-filter-container {
    margin-bottom: 24px;
}

.filter-card {
    background: white;
    border-radius: var(--border-radius);
    padding: 24px;
    box-shadow: var(--shadow);
    border: 1px solid #f1f5f9;
    transition: var(--transition);
}

.filter-card:hover {
    box-shadow: var(--shadow-hover);
}

.filter-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 16px;
    margin-bottom: 20px;
}

.filter-actions {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
}

.grades-content-container {
    margin-top: 24px;
}

.loading-container {
    text-align: center;
    padding: 60px 20px;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    min-height: 200px;
}

.loading-spinner {
    width: 40px;
    height: 40px;
    border: 4px solid #f1f5f9;
    border-top: 4px solid var(--theme-primary);
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin: 0 auto 16px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.no-students-container {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow);
    border: 1px solid #f1f5f9;
}

.no-data-icon {
    font-size: 4rem;
    color: #cbd5e1;
    margin-bottom: 16px;
    opacity: 0.7;
}

.no-students-container h3 {
    color: #1e293b;
    margin-bottom: 8px;
    font-weight: 600;
}

.no-students-container p {
    color: #64748b;
    font-size: 1rem;
}

.students-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 20px;
}

.student-card {
    background: white;
    border-radius: var(--border-radius);
    padding: 24px;
    box-shadow: var(--shadow);
    border: 1px solid #f1f5f9;
    transition: var(--transition);
    position: relative;
    overflow: hidden;
}

.student-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-hover);
}

.student-header {
    display: flex;
    align-items: flex-start;
    gap: 16px;
    margin-bottom: 16px;
    cursor: pointer;
    position: relative;
}

.student-avatar-small {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #f1f5f9;
    transition: var(--transition);
}

.student-info {
    flex: 1;
}

.student-name {
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 4px;
    font-size: 1.1rem;
    line-height: 1.3;
}

.student-id {
    color: #64748b;
    font-size: 0.9rem;
    margin-bottom: 4px;
}

.student-year {
    display: inline-block;
    background: linear-gradient(135deg, var(--theme-primary), var(--theme-secondary));
    color: white;
    padding: 4px 8px;
    border-radius: 6px;
    font-size: 0.8rem;
    font-weight: 500;
}

.student-actions-toggle {
    margin-left: auto;
    width: 32px;
    height: 32px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f1f5f9;
    color: #64748b;
    transition: var(--transition);
    cursor: pointer;
    flex-shrink: 0;
}

.student-actions-toggle:hover {
    background: var(--theme-primary);
    color: white;
    transform: scale(1.05);
}

.subjects-preview {
    margin-bottom: 12px;
}

.subject-preview-item {
    background: #f8fafc;
    padding: 10px 12px;
    border-radius: 8px;
    margin-bottom: 6px;
    border-left: 3px solid var(--theme-primary);
    transition: var(--transition);
}

.subject-preview-item:hover {
    background: #f1f5f9;
    transform: translateX(4px);
}

.more-subjects-indicator {
    text-align: center;
    padding: 8px;
    color: #64748b;
    font-size: 0.85rem;
    background: #f1f5f9;
    border-radius: 6px;
    cursor: pointer;
    transition: var(--transition);
}

.more-subjects-indicator:hover {
    background: #e2e8f0;
    color: #475569;
}

.student-subjects-expandable {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.student-subjects-expandable.expanded {
    max-height: 500px;
}

.expandable-content {
    padding-top: 12px;
    border-top: 1px solid #e2e8f0;
}

.expandable-content .subject-item {
    background: #f8fafc;
    padding: 12px;
    border-radius: 8px;
    margin-bottom: 8px;
    border: 1px solid #e2e8f0;
    transition: var(--transition);
}

.expandable-content .subject-item:hover {
    border-color: var(--theme-primary);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.expandable-content .subject-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 8px;
}

.expandable-content .subject-code {
    font-weight: 600;
    color: #1e293b;
    font-size: 0.95rem;
}

.expandable-content .subject-name {
    color: #64748b;
    font-size: 0.9rem;
    margin-bottom: 4px;
}

.expandable-content .subject-meta {
    display: flex;
    justify-content: space-between;
    margin-bottom: 12px;
    font-size: 0.8rem;
    color: #94a3b8;
}

.expandable-content .btn-grade {
    width: 100%;
    padding: 10px 16px;
    font-size: 0.9rem;
    transition: all 0.3s ease;
}

.expandable-content .btn-grade:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(67, 97, 238, 0.3);
}

/* Modal Styles */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10000;
    padding: 20px;
    backdrop-filter: blur(5px);
}

.modal-container {
    background: white;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow-hover);
    max-width: 500px;
    width: 100%;
    max-height: 90vh;
    overflow-y: auto;
    transition: all 0.3s ease;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 24px 24px 0;
    margin-bottom: 0;
}

.modal-header h3 {
    margin: 0;
    color: #1e293b;
    font-weight: 600;
}

.close-modal {
    background: none;
    border: none;
    font-size: 1.2rem;
    color: #ffffff;
    cursor: pointer;
    padding: 8px;
    border-radius: 6px;
    transition: var(--transition);
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
}

.close-modal:hover {
    background: #f1f5f9;
    color: #374151;
    transform: scale(1.1);
}

.modal-body {
    padding: 24px;
}

/* Horizontal Modal Styles */
.horizontal-modal {
    max-width: 900px;
    animation: modalSlideIn 0.3s ease-out;
}

@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: scale(0.9) translateY(-20px);
    }
    to {
        opacity: 1;
        transform: scale(1) translateY(0);
    }
}

.modal-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
    margin-top: 16px;
}

.modal-column {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.student-column {
    border-right: 1px solid #e2e8f0;
    padding-right: 24px;
}

.grade-column {
    padding-left: 0;
}

.student-info-card {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 16px;
    background: #f8fafc;
    border-radius: 12px;
    margin-bottom: 20px;
    transition: var(--transition);
}

.student-info-card:hover {
    background: #f1f5f9;
    transform: translateY(-2px);
}

.student-avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    overflow: hidden;
    border: 3px solid #e2e8f0;
    transition: var(--transition);
}

.student-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.student-details h4 {
    margin: 0 0 4px;
    color: #1e293b;
    font-weight: 600;
}

.student-id {
    color: #64748b;
    margin: 0 0 4px;
    font-size: 0.9rem;
}

.student-course {
    color: #64748b;
    margin: 0;
    font-size: 0.9rem;
}

/* Subjects List Styles */
.subjects-list-container {
    margin-top: 16px;
}

.subjects-list-container h5 {
    margin-bottom: 12px;
    color: #1e293b;
    font-size: 1rem;
    font-weight: 600;
}

.subjects-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
    max-height: 400px;
    overflow-y: auto;
    padding-right: 4px;
}

.subjects-list::-webkit-scrollbar {
    width: 4px;
}

.subjects-list::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 2px;
}

.subjects-list::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 2px;
}

.subjects-list::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

.subject-option {
    padding: 12px;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    cursor: pointer;
    transition: var(--transition);
    background: #f8fafc;
}

.subject-option:hover {
    border-color: var(--theme-primary);
    background: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.subject-option.active {
    border-color: var(--theme-primary);
    background: linear-gradient(135deg, var(--theme-primary), var(--theme-secondary));
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(67, 97, 238, 0.3);
}

.subject-option.active .subject-code,
.subject-option.active .subject-meta,
.subject-option.active .subject-name {
    color: white;
}

.subject-option-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 4px;
}

.subject-code {
    font-weight: 600;
    color: #1e293b;
    font-size: 0.95rem;
}

.subject-meta {
    font-size: 0.8rem;
    color: #64748b;
    background: rgba(255, 255, 255, 0.9);
    padding: 2px 6px;
    border-radius: 4px;
    font-weight: 500;
}

.subject-option.active .subject-meta {
    background: rgba(255, 255, 255, 0.2);
}

.subject-name {
    font-size: 0.9rem;
    color: #64748b;
    margin-bottom: 4px;
    line-height: 1.3;
}

/* Subject Info Card */
.subject-info-card,
.grade-input-card {
    background: #f8fafc;
    padding: 20px;
    border-radius: 12px;
    border: 1px solid #e2e8f0;
    transition: var(--transition);
}

.subject-info-card:hover,
.grade-input-card:hover {
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    transform: translateY(-2px);
}

.subject-info-card h5,
.grade-input-card .form-label {
    margin-bottom: 16px;
    color: #1e293b;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 8px;
}

.subject-details-grid {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.subject-detail-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid #e2e8f0;
    transition: var(--transition);
}

.subject-detail-item:hover {
    background: rgba(255, 255, 255, 0.5);
    border-radius: 6px;
    padding: 8px 12px;
    margin: 0 -12px;
}

.subject-detail-item:last-child {
    border-bottom: none;
}

.subject-detail-item .label {
    color: #64748b;
    font-weight: 500;
    font-size: 0.9rem;
}

.subject-detail-item .value {
    color: #1e293b;
    font-weight: 600;
    font-size: 0.95rem;
}

.grade-input-section {
    margin-bottom: 24px;
}

.form-text {
    color: #64748b;
    font-size: 0.85rem;
    margin-top: 4px;
    line-height: 1.4;
}

.form-actions {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    padding-top: 20px;
    border-top: 1px solid #e2e8f0;
}

/* Dark mode styles */
body.dark-mode .filter-card,
body.dark-mode .student-card,
body.dark-mode .modal-container,
body.dark-mode .no-students-container {
    background: var(--card-dark);
    border-color: var(--border-dark);
    color: var(--text-dark);
}

body.dark-mode .student-name,
body.dark-mode .subject-code,
body.dark-mode .modal-header h3,
body.dark-mode .no-students-container h3 {
    color: var(--text-dark);
}

body.dark-mode .student-id,
body.dark-mode .student-course,
body.dark-mode .subject-name,
body.dark-mode .form-text,
body.dark-mode .no-students-container p {
    color: var(--text-muted-dark);
}

body.dark-mode .student-year {
    background: linear-gradient(135deg, var(--theme-primary), var(--theme-secondary));
    color: white;
}

body.dark-mode .subject-preview-item,
body.dark-mode .expandable-content .subject-item {
    background: rgba(255, 255, 255, 0.05);
    border-color: var(--border-dark);
}

body.dark-mode .student-info-card,
body.dark-mode .subject-details,
body.dark-mode .subject-info-card,
body.dark-mode .grade-input-card {
    background: rgba(255, 255, 255, 0.05);
    border-color: var(--border-dark);
}

body.dark-mode .form-actions {
    border-top-color: var(--border-dark);
}

body.dark-mode .student-actions-toggle {
    background: rgba(255, 255, 255, 0.1);
    color: var(--text-muted-dark);
}

body.dark-mode .student-actions-toggle:hover {
    background: var(--theme-primary);
    color: white;
}

body.dark-mode .more-subjects-indicator {
    background: rgba(255, 255, 255, 0.1);
    color: var(--text-muted-dark);
}

body.dark-mode .more-subjects-indicator:hover {
    background: rgba(255, 255, 255, 0.15);
    color: var(--text-dark);
}

body.dark-mode .expandable-content {
    border-top-color: var(--border-dark);
}

body.dark-mode .student-column {
    border-right-color: var(--border-dark);
}

body.dark-mode .subject-option {
    background: rgba(255, 255, 255, 0.05);
    border-color: var(--border-dark);
    color: var(--text-dark);
}

body.dark-mode .subject-option:hover {
    background: rgba(255, 255, 255, 0.1);
    border-color: var(--theme-primary);
}

body.dark-mode .subject-option.active {
    border-color: var(--theme-primary);
    background: linear-gradient(135deg, var(--theme-primary), var(--theme-secondary));
    color: white;
}

body.dark-mode .subject-meta {
    background: rgba(255, 255, 255, 0.1);
    color: var(--text-muted-dark);
}

body.dark-mode .subject-option.active .subject-meta {
    background: rgba(255, 255, 255, 0.2);
    color: white;
}

body.dark-mode .subject-detail-item {
    border-bottom-color: var(--border-dark);
}

body.dark-mode .subject-detail-item:hover {
    background: rgba(255, 255, 255, 0.05);
}

body.dark-mode .subjects-list::-webkit-scrollbar-track {
    background: var(--border-dark);
}

body.dark-mode .subjects-list::-webkit-scrollbar-thumb {
    background: var(--text-muted-dark);
}

body.dark-mode .subjects-list::-webkit-scrollbar-thumb:hover {
    background: var(--text-dark);
}

/* ===== DARK MODE FIXES FOR GRADE MODAL ===== */
body.dark-mode .modal-container {
    background: var(--card-dark);
    border: 1px solid var(--border-dark);
    color: var(--text-dark);
}

body.dark-mode .modal-header h3 {
    color: var(--text-dark);
}

body.dark-mode .close-modal {
    color: var(--text-muted-dark);
    background: rgba(255, 255, 255, 0.1);
}

body.dark-mode .close-modal:hover {
    background: rgba(255, 255, 255, 0.2);
    color: white;
}

/* Student info card in modal */
body.dark-mode .student-info-card {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid var(--border-dark);
}

body.dark-mode .student-details h4 {
    color: var(--text-dark);
}

body.dark-mode .student-details p {
    color: var(--text-muted-dark);
}

/* Subjects list container */
body.dark-mode .subjects-list-container h5 {
    color: var(--text-dark);
}

body.dark-mode .subject-option {
    background: rgba(255, 255, 255, 0.05);
    border: 2px solid var(--border-dark);
    color: var(--text-dark);
}

body.dark-mode .subject-option:hover {
    border-color: var(--theme-primary);
    background: rgba(255, 255, 255, 0.1);
}

body.dark-mode .subject-option.active {
    background: linear-gradient(135deg, var(--theme-primary), var(--theme-secondary));
    border-color: var(--theme-primary);
    color: white;
}

body.dark-mode .subject-option.active .subject-code,
body.dark-mode .subject-option.active .subject-name {
    color: white;
}

body.dark-mode .subject-code {
    color: var(--text-dark);
}

body.dark-mode .subject-name {
    color: var(--text-muted-dark);
}

body.dark-mode .subject-meta {
    background: rgba(255, 255, 255, 0.1);
    color: var(--text-muted-dark);
}

body.dark-mode .subject-option.active .subject-meta {
    background: rgba(255, 255, 255, 0.2);
    color: white;
}

/* Subject info card */
body.dark-mode .subject-info-card,
body.dark-mode .grade-input-card {
    background: rgba(255, 255, 255, 0.05);
    border: 1px solid var(--border-dark);
}

body.dark-mode .subject-info-card h5,
body.dark-mode .grade-input-card .form-label {
    color: var(--text-dark);
}

body.dark-mode .subject-detail-item .label {
    color: var(--text-muted-dark);
}

body.dark-mode .subject-detail-item .value {
    color: var(--text-dark);
    font-weight: 600;
}

/* Form elements */
body.dark-mode .form-label {
    color: var(--text-dark);
}

body.dark-mode .form-label i {
    color: var(--theme-accent);
}

body.dark-mode .form-control {
    background: var(--dark-color);
    border: 2px solid var(--border-dark);
    color: var(--text-dark);
}

body.dark-mode .form-control:focus {
    background: var(--dark-color);
    border-color: var(--theme-primary);
    box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
    color: var(--text-dark);
}

body.dark-mode select.form-control {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%2394a3b8' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
}

body.dark-mode .form-text {
    color: var(--text-muted-dark);
}

/* Form actions */
body.dark-mode .form-actions {
    border-top-color: var(--border-dark);
}

/* Modal grid borders */
body.dark-mode .student-column {
    border-right-color: var(--border-dark);
}

/* No data states */
body.dark-mode .no-subjects {
    color: var(--text-muted-dark);
}

/* Scrollbar for subjects list in dark mode */
body.dark-mode .subjects-list::-webkit-scrollbar-track {
    background: var(--border-dark);
}

body.dark-mode .subjects-list::-webkit-scrollbar-thumb {
    background: var(--text-muted-dark);
}

body.dark-mode .subjects-list::-webkit-scrollbar-thumb:hover {
    background: var(--text-dark);
}

/* Ensure select options are visible in dark mode */
body.dark-mode select.form-control option {
    background: var(--card-dark);
    color: var(--text-dark);
}

/* Fix for the modal overlay background */
body.dark-mode .modal-overlay {
    background: rgba(0, 0, 0, 0.7);
    backdrop-filter: blur(5px);
}

/* Styles for graded subjects */
.subject-preview-item.graded {
    border-left-color: #10b981;
    background: rgba(16, 185, 129, 0.05);
}

.expandable-content .subject-item.graded {
    border-left-color: #10b981;
    background: rgba(16, 185, 129, 0.05);
}

.subject-option.graded {
    border-left-color: #10b981;
    background: rgba(16, 185, 129, 0.05);
}

.subject-option.graded .subject-grade-badge {
    background: #10b981;
    color: white;
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 0.8rem;
    font-weight: 600;
}

.grade-badge {
    background: #10b981;
    color: white;
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 0.7rem;
    font-weight: 600;
}

/* Dark mode support for graded subjects */
body.dark-mode .subject-preview-item.graded {
    background: rgba(16, 185, 129, 0.1);
}

body.dark-mode .expandable-content .subject-item.graded {
    background: rgba(16, 185, 129, 0.1);
}

body.dark-mode .subject-option.graded {
    background: rgba(16, 185, 129, 0.1);
}

/*  */
/* Light mode - ensure graded subjects have visible text */
.subject-preview-item.graded .subject-code,
.subject-preview-item.graded .subject-name {
    color: #1e293b !important;
}

.expandable-content .subject-item.graded .subject-code,
.expandable-content .subject-item.graded .subject-name {
    color: #1e293b !important;
}

/* Dark mode - ensure graded subjects have visible text */
body.dark-mode .subject-preview-item.graded .subject-code,
body.dark-mode .subject-preview-item.graded .subject-name {
    color: var(--text-dark) !important;
}

body.dark-mode .expandable-content .subject-item.graded .subject-code,
body.dark-mode .expandable-content .subject-item.graded .subject-name {
    color: var(--text-dark) !important;
}

/* Fix for grade badge text color in both modes */
.grade-badge {
    background: #10b981 !important;
    color: white !important;
    padding: 2px 6px !important;
    border-radius: 4px !important;
    font-size: 0.7rem !important;
    font-weight: 600 !important;
}

/* Ensure the grade badge text is always white */
body.dark-mode .grade-badge,
body.dark-mode .subject-grade-badge {
    color: white !important;
    background: #10b981 !important;
}

/* ===== FIX FOR MODAL TEXT COLORS ===== */

/* Ensure subject details are visible in dark mode */
body.dark-mode .subject-detail-item .value {
    color: var(--text-dark) !important;
    font-weight: 600;
}

body.dark-mode .subject-detail-item .label {
    color: var(--text-muted-dark) !important;
}

/* Fix form text colors in modal */
body.dark-mode .form-text {
    color: var(--text-muted-dark) !important;
}

/* Ensure select options are visible in dark mode */
body.dark-mode select.form-control option {
    background: var(--card-dark) !important;
    color: var(--text-dark) !important;
}

body.dark-mode select.form-control {
    color: var(--text-dark) !important;
}

/* Fix the subject option text colors in modal */
body.dark-mode .subject-option .subject-code {
    color: var(--text-dark) !important;
}

body.dark-mode .subject-option .subject-name {
    color: var(--text-muted-dark) !important;
}

/* Active subject option in modal - ensure text is visible */
body.dark-mode .subject-option.active .subject-code,
body.dark-mode .subject-option.active .subject-name {
    color: white !important;
}

/* ===== FIX FOR STUDENT CARD TEXT COLORS ===== */

/* Ensure student info is visible in dark mode */
body.dark-mode .student-info .student-name {
    color: var(--text-dark) !important;
}

body.dark-mode .student-info .student-id {
    color: var(--text-muted-dark) !important;
}

/* Fix for expandable content text */
body.dark-mode .expandable-content .subject-header .subject-code {
    color: var(--text-dark) !important;
}

body.dark-mode .expandable-content .subject-header .subject-name {
    color: var(--text-muted-dark) !important;
}

body.dark-mode .expandable-content .subject-meta {
    color: var(--text-muted-dark) !important;
}

/* ===== ENHANCED GRADED SUBJECTS STYLING ===== */

/* Better contrast for graded subjects in light mode */
.subject-preview-item.graded {
    border-left-color: #10b981 !important;
    background: rgba(16, 185, 129, 0.08) !important;
}

.expandable-content .subject-item.graded {
    border-left-color: #10b981 !important;
    background: rgba(16, 185, 129, 0.08) !important;
}

/* Better contrast for graded subjects in dark mode */
body.dark-mode .subject-preview-item.graded {
    border-left-color: #10b981 !important;
    background: rgba(16, 185, 129, 0.15) !important;
}

body.dark-mode .expandable-content .subject-item.graded {
    border-left-color: #10b981 !important;
    background: rgba(16, 185, 129, 0.15) !important;
}

/* Fix for the more subjects indicator */
body.dark-mode .more-subjects-indicator {
    color: var(--text-muted-dark) !important;
}

/* Ensure filter form labels are visible */
body.dark-mode .grades-filter-container .form-label {
    color: var(--text-dark) !important;
}

body.dark-mode .grades-filter-container .form-control {
    color: var(--text-dark) !important;
    background: var(--dark-color) !important;
}

body.dark-mode .grades-filter-container .form-control::placeholder {
    color: var(--text-muted-dark) !important;
}

/*  */
/* Immediate fix for available subjects text visibility */
.subject-option.active {
    background: linear-gradient(135deg, var(--theme-primary), var(--theme-secondary)) !important;
    color: white !important;
    border-color: var(--theme-primary) !important;
}

.subject-option.active .subject-code,
.subject-option.active .subject-name,
.subject-option.active .subject-meta {
    color: white !important;
}

.subject-option:not(.active) .subject-code {
    color: #1e293b !important;
}

.subject-option:not(.active) .subject-name {
    color: #64748b !important;
}

.subjects-list-container h5 {
    color: #1e293b !important;
}
/*  */

/* Enhanced Mobile Responsiveness */
@media (max-width: 768px) {
    .grades-filter-container {
        margin-bottom: 16px;
    }
    
    .filter-card {
        padding: 16px;
        border-radius: 14px;
    }
    
    .filter-row {
        grid-template-columns: 1fr;
        gap: 12px;
        margin-bottom: 16px;
    }
    
    .filter-actions {
        flex-direction: column;
        gap: 8px;
    }
    
    .filter-actions button {
        width: 100%;
        justify-content: center;
    }
    
    .grades-content-container {
        margin-top: 16px;
    }
    
    .loading-container {
        padding: 40px 16px;
        min-height: 150px;
    }
    
    .loading-spinner {
        width: 32px;
        height: 32px;
    }
    
    .no-students-container {
        padding: 40px 16px;
        border-radius: 14px;
    }
    
    .no-data-icon {
        font-size: 3rem;
    }
    
    .students-grid {
        grid-template-columns: 1fr;
        gap: 16px;
        padding: 8px 0;
    }
    
    .student-card {
        padding: 16px;
        border-radius: 14px;
        margin: 0 4px;
    }
    
    .student-header {
        flex-direction: column;
        text-align: center;
        gap: 12px;
    }
    
    .student-actions-toggle {
        position: absolute;
        top: 16px;
        right: 16px;
        width: 36px;
        height: 36px;
    }
    
    .student-info {
        width: 100%;
    }
    
    .student-avatar-small {
        width: 50px;
        height: 50px;
    }
    
    .student-name {
        font-size: 1rem;
    }
    
    .subjects-preview {
        margin-top: 12px;
    }
    
    .subject-preview-item {
        padding: 8px 10px;
        font-size: 0.9rem;
    }
    
    .expandable-content .subject-item {
        padding: 12px;
    }
    
    .expandable-content .subject-header {
        flex-direction: column;
        gap: 4px;
    }
    
    .expandable-content .subject-meta {
        flex-direction: column;
        gap: 4px;
        margin-bottom: 8px;
    }
    
    .expandable-content .btn-grade {
        font-size: 0.85rem;
        padding: 10px;
    }
    
    /* Enhanced Modal for Mobile */
    .modal-overlay {
        padding: 0;
        align-items: flex-end;
        background: rgba(0, 0, 0, 0.7);
        backdrop-filter: blur(8px);
    }
    
    .modal-container {
        margin: 0;
        max-width: 100%;
        max-height: 90vh;
        border-radius: 20px 20px 0 0;
        animation: slideUp 0.3s ease-out;
    }
    
    @keyframes slideUp {
        from {
            transform: translateY(100%);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }
    
    .horizontal-modal {
        max-width: 100%;
        margin: 0;
    }
    
    .modal-grid {
        grid-template-columns: 1fr;
        gap: 16px;
    }
    
    .student-column {
        border-right: none;
        border-bottom: 1px solid #e2e8f0;
        padding-right: 0;
        padding-bottom: 16px;
        margin-bottom: 16px;
    }
    
    .grade-column {
        padding-left: 0;
    }
    
    .subjects-list {
        min-height: 100px;
        -webkit-overflow-scrolling: touch;
    }
    
    .modal-header {
        padding: 16px 16px 0;
        flex-direction: column;
        gap: 12px;
        text-align: center;
    }
    
    .modal-header h3 {
        font-size: 1.3rem;
    }
    
    .modal-body {
        padding: 16px;
    }
    
    .student-info-card {
        flex-direction: column;
        text-align: center;
        padding: 16px;
    }
    
    .student-avatar {
        width: 60px;
        height: 60px;
        margin: 0 auto 12px;
    }
    
    .subject-details-grid {
        gap: 8px;
    }
    
    .subject-detail-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 4px;
        padding: 8px 0;
    }
    
    .subject-detail-item .label {
        font-size: 0.85rem;
    }
    
    .subject-detail-item .value {
        font-size: 0.9rem;
        font-weight: 600;
    }
    
    .subject-info-card,
    .grade-input-card {
        padding: 16px;
        border-radius: 10px;
    }
    
    .grade-input-card .form-label {
        font-size: 0.95rem;
    }
    
    .form-actions {
        flex-direction: column;
        gap: 8px;
    }
    
    .form-actions button {
        width: 100%;
        min-height: 44px;
    }
    
    .close-modal {
        width: 44px;
        height: 44px;
    }

    /* Improved subjects list container */
    .subjects-list-container {
        margin-top: 12px;
    }
    
    .subjects-list-container h5 {
        font-size: 1.1rem;
        margin-bottom: 12px;
        padding: 0 8px;
    }
    
    .subjects-list {
        min-height: 100px;
        gap: 8px;
        padding: 0 4px;
        -webkit-overflow-scrolling: touch;
        scroll-behavior: smooth;
    }
    
    /* Enhanced subject options for mobile */
    .subject-option {
        padding: 16px 12px;
        min-height: 150px;
        border-radius: 12px;
        margin: 0 4px;
        border: 2px solid #e2e8f0;
        transition: all 0.2s ease;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    
    .subject-option:active {
        transform: scale(0.98);
        background: #f1f5f9;
    }
    
    .subject-option-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 6px;
        flex-wrap: wrap;
        gap: 8px;
    }
    
    .subject-code {
        font-size: 1rem;
        font-weight: 700;
        color: #1e293b;
        flex: 1;
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    
    .subject-meta {
        font-size: 0.8rem;
        color: #64748b;
        background: rgba(255, 255, 255, 0.9);
        padding: 4px 8px;
        border-radius: 6px;
        font-weight: 600;
        white-space: nowrap;
        flex-shrink: 0;
    }
    
    .subject-name {
        font-size: 0.9rem;
        color: #64748b;
        line-height: 1.3;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
        margin-bottom: 0;
    }
    
    /* Active state for mobile */
    .subject-option.active {
        border-color: var(--theme-primary);
        background: linear-gradient(135deg, var(--theme-primary), var(--theme-secondary));
        transform: scale(0.98);
    }
    
    .subject-option.active .subject-code,
    .subject-option.active .subject-name,
    .subject-option.active .subject-meta {
        color: white;
    }
    
    .subject-option.active .subject-meta {
        background: rgba(255, 255, 255, 0.2);
    }
    
    /* Improved scrollbar for mobile */
    .subjects-list::-webkit-scrollbar {
        width: 3px;
    }
    
    .subjects-list::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 3px;
    }
    
    .subjects-list::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 3px;
    }
    
    .subjects-list::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }

    body.dark-mode .modal-container {
        background: var(--card-dark);
        border: 1px solid var(--border-dark);
    }
    
    body.dark-mode .student-column {
        border-bottom-color: var(--border-dark);
    }
    
    body.dark-mode .subject-option:active {
        background: rgba(255, 255, 255, 0.1);
    }
}

@media (max-width: 480px) {
    .horizontal-modal {
        margin: 0;
        max-width: 100%;
        border-radius: 16px 16px 0 0;
    }
    
    .modal-header {
        padding: 12px 12px 0;
    }
    
    .modal-body {
        padding: 12px;
    }
    
    .student-column,
    .grade-column {
        padding: 0;
    }
    
    .subjects-list-container h5,
    .subject-info-card h5 {
        font-size: 1rem;
        margin-bottom: 12px;
    }
    
    .subject-option {
        padding: 10px;
        min-height: 150px;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }
    
    .subject-option-header {
        flex-direction: column;
        gap: 4px;
        align-items: flex-start;
    }
    
    /* Enhanced touch targets */
    .btn-primary,
    .btn-secondary,
    .btn-cancel,
    .btn-grade {
        min-height: 44px;
        padding: 12px 16px;
        font-size: 16px;
    }
    
    /* Improved form elements for mobile */
    select.form-control,
    input.form-control {
        font-size: 16px;
        border-radius: 10px;
        min-height: 44px;
    }
    
    select.form-control {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='%2364748b' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 12px center;
        background-size: 12px;
        padding-right: 40px;
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
    }
    
    .form-label {
        font-size: 0.95rem;
        margin-bottom: 6px;
    }
    
    .form-text {
        font-size: 0.8rem;
    }
    
    /* Student cards for very small screens */
    .students-grid {
        grid-template-columns: 1fr;
        gap: 12px;
    }
    
    .student-card {
        margin: 0 2px;
    }
    
    .student-avatar-small {
        width: 45px;
        height: 45px;
    }
    
    .student-name {
        font-size: 0.95rem;
    }
    
    .student-id {
        font-size: 0.8rem;
    }
    
    .student-year {
        font-size: 0.75rem;
        padding: 3px 6px;
    }
    
    /* Enhanced expandable section */
    .student-subjects-expandable.expanded {
        max-height: 400px;
    }
    
    .expandable-content {
        padding-top: 16px;
    }

    /* Extra small devices */
    .subjects-list {
        min-height: 100px;
        gap: 8px;
    }
    
    .subject-option {
        padding: 14px 10px;
        min-height: 150px;
        border-radius: 10px;
    }
    
    .subject-option-header {
        margin-bottom: 4px;
    }
    
    .subject-code {
        font-size: 0.95rem;
    }
    
    .subject-name {
        font-size: 0.85rem;
        -webkit-line-clamp: 2;
    }
    
    .subject-meta {
        font-size: 0.75rem;
        padding: 3px 6px;
    }
    
    /* Improved touch targets */
    .subject-option {
        min-height: 150px; /* Minimum touch target size */
    }
}

/* Enhanced Touch Interactions for Mobile */
@media (hover: none) and (pointer: coarse) {
    .subject-option {
        min-height: 150px;
    }
    
    .student-actions-toggle {
        min-width: 44px;
        min-height: 44px;
    }
    
    .btn-grade {
        min-height: 44px;
    }
    
    /* Remove hover effects on touch devices */
    .subject-option:hover {
        transform: none;
    }
    
    .student-card:hover {
        transform: none;
    }
    
    .student-info-card:hover {
        transform: none;
    }
    
    .subject-info-card:hover,
    .grade-input-card:hover {
        transform: none;
    }
    
    .expandable-content .subject-item:hover {
        transform: none;
    }
    
    /* Add active states for touch feedback */
    .subject-option:active {
        background: var(--theme-primary);
        color: white;
        transform: scale(0.98);
    }
    
    .student-actions-toggle:active {
        background: var(--theme-primary);
        transform: scale(0.95);
    }
    
    .btn-grade:active {
        transform: scale(0.98);
    }
    
    .student-card:active {
        transform: scale(0.99);
    }

    .subject-option {
        cursor: pointer;
        -webkit-tap-highlight-color: transparent;
    }
    
    .subject-option:active {
        background: #f1f5f9;
        transform: scale(0.98);
        transition: transform 0.1s ease;
    }
    
    .subject-option.active:active {
        background: linear-gradient(135deg, var(--theme-primary), var(--theme-secondary));
        transform: scale(0.96);
    }
}

/* Professional Loading States for Mobile */
@media (max-width: 768px) {
    .loading-container p {
        margin-top: 16px;
        font-size: 1rem;
        color: #64748b;
        font-weight: 500;
    }
}

/* Dark Mode Mobile Enhancements */
@media (max-width: 768px) {
    body.dark-mode .student-column {
        border-bottom-color: var(--border-dark);
    }
    
    body.dark-mode .modal-header {
        background: var(--card-dark);
        border-bottom: 1px solid var(--border-dark);
    }
    
    body.dark-mode .modal-body {
        background: var(--card-dark);
    }
    
    body.dark-mode .modal-container {
        background: var(--card-dark);
    }

    body.dark-mode .subject-option {
        border-color: var(--border-dark);
        background: rgba(255, 255, 255, 0.05);
    }
    
    body.dark-mode .subject-option:active {
        background: rgba(255, 255, 255, 0.1);
    }
    
    body.dark-mode .subject-option.active {
        border-color: var(--theme-primary);
        background: linear-gradient(135deg, var(--theme-primary), var(--theme-secondary));
    }
    
    body.dark-mode .subject-meta {
        background: rgba(255, 255, 255, 0.1);
        color: var(--text-muted-dark);
    }
    
    body.dark-mode .subject-option.active .subject-meta {
        background: rgba(255, 255, 255, 0.2);
    }
}

/* Improved modal layout for subjects on mobile */
@media (max-width: 768px) {
    .modal-column.student-column {
        border-bottom: 1px solid #e2e8f0;
        padding-bottom: 20px;
        margin-bottom: 20px;
    }
    
    body.dark-mode .modal-column.student-column {
        border-bottom-color: var(--border-dark);
    }
    
    /* Ensure the subjects list doesn't overflow */
    .subjects-list {
        min-height: 100px;
    }
}

/* Loading state for subjects */
.subjects-loading {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 40px 20px;
    color: #64748b;
    font-size: 0.9rem;
}

.subjects-loading .loading-spinner {
    width: 20px;
    height: 20px;
    margin-right: 8px;
}

/* Empty state for subjects */
.no-subjects {
    text-align: center;
    padding: 40px 20px;
    color: #64748b;
    font-size: 0.9rem;
}

.no-subjects i {
    font-size: 2rem;
    margin-bottom: 8px;
    opacity: 0.5;
}

/* Print Styles */
@media print {
    .modal-overlay {
        position: static;
        background: white;
    }
    
    .modal-container {
        box-shadow: none;
        max-width: none;
        max-height: none;
    }
    
    .student-card {
        break-inside: avoid;
    }
}

/* High contrast mode support */
@media (prefers-contrast: high) {
    .student-card {
        border: 2px solid #1e293b;
    }
    
    .subject-option {
        border: 2px solid #1e293b;
    }
    
    .modal-container {
        border: 2px solid #1e293b;
    }
}

/* Reduced motion support */
@media (prefers-reduced-motion: reduce) {
    .student-card,
    .subject-option,
    .modal-container,
    .btn-primary,
    .btn-secondary {
        transition: none;
    }
    
    .loading-spinner {
        animation: spin 2s linear infinite;
    }
    
    .student-subjects-expandable {
        transition: max-height 0.1s ease;
    }
    
    .modal-container {
        animation: none;
    }
}

</style>