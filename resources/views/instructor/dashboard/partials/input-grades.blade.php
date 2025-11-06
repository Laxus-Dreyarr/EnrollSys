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
                    <label for="semester" class="form-label">
                        <i class="fas fa-calendar-alt"></i>
                        Semester
                    </label>
                    <select name="semester" id="semester" class="form-control">
                        <option value="">All Semesters</option>
                        <option value="1st Sem">1st Semester</option>
                        <option value="2nd Sem">2nd Semester</option>
                        <option value="Summer">Summer</option>
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
    <div class="modal-container">
        <div class="modal-header">
            <h3>Input Grade</h3>
            <button class="close-modal" id="close-grade-modal">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-body">
            <form id="grade-form">
                <input type="hidden" id="grade_student_id" name="student_id">
                <input type="hidden" id="grade_subject_id" name="subject_id">
                
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
                
                <div class="subject-info">
                    <h5>Subject Information</h5>
                    <div class="subject-details">
                        <div class="subject-item">
                            <span class="label">Subject Code:</span>
                            <span class="value" id="subject_code"></span>
                        </div>
                        <div class="subject-item">
                            <span class="label">Subject Name:</span>
                            <span class="value" id="subject_name"></span>
                        </div>
                        <div class="subject-item">
                            <span class="label">Units:</span>
                            <span class="value" id="subject_units"></span>
                        </div>
                        <div class="subject-item">
                            <span class="label">Year Level:</span>
                            <span class="value" id="subject_year"></span>
                        </div>
                        <div class="subject-item">
                            <span class="label">Semester:</span>
                            <span class="value" id="subject_semester"></span>
                        </div>
                    </div>
                </div>
                
                <div class="grade-input-section">
                    <label for="grade" class="form-label">
                        <i class="fas fa-pen"></i>
                        Grade
                    </label>
                    <select name="grade" id="grade" class="form-control" required>
                        <option value="">Select Grade</option>
                        <option value="1.00">1.00 - Excellent</option>
                        <option value="1.25">1.25 - Very Good</option>
                        <option value="1.50">1.50 - Good</option>
                        <option value="1.75">1.75 - Satisfactory</option>
                        <option value="2.00">2.00 - Fair</option>
                        <option value="2.25">2.25 - Pass</option>
                        <option value="2.50">2.50 - Conditional</option>
                        <option value="2.75">2.75 - Conditional</option>
                        <option value="3.00">3.00 - Conditional</option>
                        <option value="4.00">4.00 - Conditional</option>
                        <option value="5.00">5.00 - Failed</option>
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
}

.no-data-icon {
    font-size: 4rem;
    color: #cbd5e1;
    margin-bottom: 16px;
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
}

.student-avatar-small {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    object-fit: cover;
    border: 3px solid #f1f5f9;
}

.student-info {
    flex: 1;
}

.student-name {
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 4px;
    font-size: 1.1rem;
}

.student-id {
    color: #64748b;
    font-size: 0.9rem;
    margin-bottom: 4px;
}

.student-year {
    display: inline-block;
    background: #f1f5f9;
    color: #475569;
    padding: 4px 8px;
    border-radius: 6px;
    font-size: 0.8rem;
    font-weight: 500;
}

.student-subjects {
    margin-top: 16px;
}

.subject-item {
    background: #f8fafc;
    padding: 12px;
    border-radius: 8px;
    margin-bottom: 8px;
    border-left: 4px solid var(--theme-primary);
}

.subject-code {
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 4px;
}

.subject-name {
    color: #64748b;
    font-size: 0.9rem;
    margin-bottom: 4px;
}

.subject-meta {
    display: flex;
    justify-content: space-between;
    font-size: 0.8rem;
    color: #94a3b8;
}

.student-actions {
    margin-top: 16px;
    display: flex;
    gap: 8px;
}

.btn-grade {
    flex: 1;
    padding: 10px 16px;
    font-size: 0.9rem;
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
}

.modal-container {
    background: white;
    border-radius: var(--border-radius);
    box-shadow: var(--shadow-hover);
    max-width: 500px;
    width: 100%;
    max-height: 90vh;
    overflow-y: auto;
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
}

.close-modal {
    background: none;
    border: none;
    font-size: 1.2rem;
    color: #64748b;
    cursor: pointer;
    padding: 8px;
    border-radius: 6px;
    transition: var(--transition);
}

.close-modal:hover {
    background: #f1f5f9;
    color: #374151;
}

.modal-body {
    padding: 24px;
}

.student-info-card {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 16px;
    background: #f8fafc;
    border-radius: 12px;
    margin-bottom: 20px;
}

.student-avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    overflow: hidden;
}

.student-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.student-details h4 {
    margin: 0 0 4px;
    color: #1e293b;
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

.subject-info {
    margin-bottom: 20px;
}

.subject-info h5 {
    color: #1e293b;
    margin-bottom: 12px;
    font-size: 1rem;
}

.subject-details {
    background: #f8fafc;
    padding: 16px;
    border-radius: 8px;
}

.subject-item {
    display: flex;
    justify-content: space-between;
    margin-bottom: 8px;
}

.subject-item:last-child {
    margin-bottom: 0;
}

.subject-item .label {
    color: #64748b;
    font-weight: 500;
}

.subject-item .value {
    color: #1e293b;
    font-weight: 600;
}

.grade-input-section {
    margin-bottom: 24px;
}

.form-text {
    color: #64748b;
    font-size: 0.85rem;
    margin-top: 4px;
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
body.dark-mode .modal-container {
    background: var(--card-dark);
    border-color: var(--border-dark);
    color: var(--text-dark);
}

body.dark-mode .student-name,
body.dark-mode .subject-code,
body.dark-mode .modal-header h3 {
    color: var(--text-dark);
}

body.dark-mode .student-id,
body.dark-mode .student-course,
body.dark-mode .subject-name,
body.dark-mode .form-text {
    color: var(--text-muted-dark);
}

body.dark-mode .student-year {
    background: rgba(255, 255, 255, 0.1);
    color: var(--text-muted-dark);
}

body.dark-mode .subject-item {
    background: rgba(255, 255, 255, 0.05);
}

body.dark-mode .student-info-card,
body.dark-mode .subject-details {
    background: rgba(255, 255, 255, 0.05);
}

body.dark-mode .form-actions {
    border-top-color: var(--border-dark);
}

@media (max-width: 768px) {
    .filter-row {
        grid-template-columns: 1fr;
    }
    
    .students-grid {
        grid-template-columns: 1fr;
    }
    
    .modal-container {
        margin: 20px;
        max-height: calc(100vh - 40px);
    }
    
    .student-header {
        flex-direction: column;
        text-align: center;
    }
    
    .student-info-card {
        flex-direction: column;
        text-align: center;
    }
}
</style>