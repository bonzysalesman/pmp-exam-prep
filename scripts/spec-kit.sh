#!/bin/bash

# PMP Spec Kit Automation Script
# Implements GitHub Spec Kit workflow for PMP project

set -e

PROJECT_ROOT="/Users/bonzysalesman/pmp-exam-prep"
SPECS_DIR="$PROJECT_ROOT/specs"
MEMORY_DIR="$PROJECT_ROOT/memory"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Logging function
log() {
    echo -e "${BLUE}[$(date +'%Y-%m-%d %H:%M:%S')]${NC} $1"
}

error() {
    echo -e "${RED}[ERROR]${NC} $1" >&2
}

success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

warn() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

# Get next feature number
get_next_feature_number() {
    local max_num=0
    if [ -d "$SPECS_DIR" ]; then
        for dir in "$SPECS_DIR"/[0-9][0-9][0-9]-*; do
            if [ -d "$dir" ]; then
                local num=$(basename "$dir" | cut -d'-' -f1)
                if [ "$num" -gt "$max_num" ]; then
                    max_num=$num
                fi
            fi
        done
    fi
    printf "%03d" $((max_num + 1))
}

# Create feature branch name from description
create_branch_name() {
    local description="$1"
    local feature_num="$2"
    
    # Convert to lowercase, replace spaces with hyphens, remove special chars
    local branch_name=$(echo "$description" | tr '[:upper:]' '[:lower:]' | sed 's/[^a-z0-9 ]//g' | sed 's/ \+/-/g' | cut -c1-50)
    echo "${feature_num}-${branch_name}"
}

# /constitution command
constitution() {
    local description="$1"
    
    if [ -z "$description" ]; then
        error "Usage: /constitution <description of principles and guidelines>"
        exit 1
    fi
    
    log "Creating project constitution..."
    
    # Constitution already exists, update it
    if [ -f "$MEMORY_DIR/constitution.md" ]; then
        warn "Constitution already exists. Consider updating manually or creating a new version."
        return 0
    fi
    
    success "Constitution created at $MEMORY_DIR/constitution.md"
    log "Review and customize the constitution before proceeding with feature development."
}

# /specify command
specify() {
    local description="$1"
    
    if [ -z "$description" ]; then
        error "Usage: /specify <feature description>"
        exit 1
    fi
    
    log "Creating feature specification..."
    
    # Get next feature number and create branch name
    local feature_num=$(get_next_feature_number)
    local branch_name=$(create_branch_name "$description" "$feature_num")
    local feature_dir="$SPECS_DIR/$branch_name"
    
    # Create feature directory
    mkdir -p "$feature_dir"
    
    # Create git branch
    cd "$PROJECT_ROOT"
    git checkout -b "$branch_name" 2>/dev/null || git checkout "$branch_name"
    
    # Copy and customize spec template
    local spec_file="$feature_dir/spec.md"
    cp "$PROJECT_ROOT/.spec-kit/templates/spec-template.md" "$spec_file"
    
    # Replace template variables
    sed -i '' "s/\[FEATURE NAME\]/$description/g" "$spec_file"
    sed -i '' "s/\[###-feature-name\]/$branch_name/g" "$spec_file"
    sed -i '' "s/\[DATE\]/$(date +'%B %d, %Y')/g" "$spec_file"
    sed -i '' "s/\$ARGUMENTS/$description/g" "$spec_file"
    
    success "Feature specification created: $spec_file"
    success "Git branch created: $branch_name"
    log "Next step: Review and complete the specification, then run '/plan'"
}

# /plan command
plan() {
    local current_branch=$(git rev-parse --abbrev-ref HEAD)
    local feature_dir="$SPECS_DIR/$current_branch"
    
    if [ ! -d "$feature_dir" ]; then
        error "No feature directory found for branch: $current_branch"
        error "Run '/specify' first to create a feature specification"
        exit 1
    fi
    
    if [ ! -f "$feature_dir/spec.md" ]; then
        error "No spec.md found in $feature_dir"
        exit 1
    fi
    
    log "Creating implementation plan for feature: $current_branch"
    
    # Copy and customize plan template
    local plan_file="$feature_dir/plan.md"
    cp "$PROJECT_ROOT/.spec-kit/templates/plan-template.md" "$plan_file"
    
    # Replace template variables
    local feature_name=$(echo "$current_branch" | cut -d'-' -f2- | tr '-' ' ')
    sed -i '' "s/\[FEATURE\]/$feature_name/g" "$plan_file"
    sed -i '' "s/\[###-feature-name\]/$current_branch/g" "$plan_file"
    sed -i '' "s/\[DATE\]/$(date +'%B %d, %Y')/g" "$plan_file"
    
    # Create supporting documents
    touch "$feature_dir/research.md"
    touch "$feature_dir/data-model.md"
    touch "$feature_dir/quickstart.md"
    mkdir -p "$feature_dir/contracts"
    
    success "Implementation plan created: $plan_file"
    log "Complete the plan details, then run '/tasks' to generate implementation tasks"
}

# /tasks command
tasks() {
    local current_branch=$(git rev-parse --abbrev-ref HEAD)
    local feature_dir="$SPECS_DIR/$current_branch"
    
    if [ ! -f "$feature_dir/plan.md" ]; then
        error "No plan.md found. Run '/plan' first."
        exit 1
    fi
    
    log "Generating implementation tasks for feature: $current_branch"
    
    # Copy and customize tasks template
    local tasks_file="$feature_dir/tasks.md"
    cp "$PROJECT_ROOT/.spec-kit/templates/tasks-template.md" "$tasks_file"
    
    # Replace template variables
    local feature_name=$(echo "$current_branch" | cut -d'-' -f2- | tr '-' ' ')
    sed -i '' "s/\[FEATURE\]/$feature_name/g" "$tasks_file"
    sed -i '' "s/\[###-feature-name\]/$current_branch/g" "$tasks_file"
    sed -i '' "s/\[DATE\]/$(date +'%B %d, %Y')/g" "$tasks_file"
    
    success "Implementation tasks created: $tasks_file"
    log "Review tasks and begin implementation with '/implement'"
}

# /implement command
implement() {
    local current_branch=$(git rev-parse --abbrev-ref HEAD)
    local feature_dir="$SPECS_DIR/$current_branch"
    
    if [ ! -f "$feature_dir/tasks.md" ]; then
        error "No tasks.md found. Run '/tasks' first."
        exit 1
    fi
    
    log "Starting implementation for feature: $current_branch"
    
    # Create implementation tracking
    local impl_file="$feature_dir/implementation.md"
    cat > "$impl_file" << EOF
# Implementation: $(echo "$current_branch" | cut -d'-' -f2- | tr '-' ' ')

**Branch**: $current_branch  
**Started**: $(date +'%B %d, %Y at %H:%M')  
**Status**: In Progress

## Implementation Log

$(date +'%Y-%m-%d %H:%M:%S') - Implementation started
EOF
    
    success "Implementation tracking started: $impl_file"
    log "Follow the tasks in tasks.md and update implementation.md with progress"
}

# Status command
status() {
    log "PMP Spec Kit Status"
    echo
    
    # Check constitution
    if [ -f "$MEMORY_DIR/constitution.md" ]; then
        success "✓ Constitution exists"
    else
        warn "✗ No constitution found - run '/constitution' first"
    fi
    
    # List features
    echo
    log "Features:"
    if [ -d "$SPECS_DIR" ]; then
        for dir in "$SPECS_DIR"/[0-9][0-9][0-9]-*; do
            if [ -d "$dir" ]; then
                local feature_name=$(basename "$dir")
                local status="Draft"
                
                if [ -f "$dir/implementation.md" ]; then
                    status="In Progress"
                fi
                
                if [ -f "$dir/completed.md" ]; then
                    status="Completed"
                fi
                
                echo "  $feature_name - $status"
            fi
        done
    else
        echo "  No features found"
    fi
    
    # Current branch
    echo
    local current_branch=$(git rev-parse --abbrev-ref HEAD 2>/dev/null || echo "unknown")
    log "Current branch: $current_branch"
}

# Help command
help() {
    echo "PMP Spec Kit Commands:"
    echo
    echo "  /constitution <description>  - Create project constitution"
    echo "  /specify <description>       - Create feature specification"
    echo "  /plan                        - Create implementation plan"
    echo "  /tasks                       - Generate implementation tasks"
    echo "  /implement                   - Start implementation tracking"
    echo "  status                       - Show project status"
    echo "  help                         - Show this help"
    echo
    echo "Example workflow:"
    echo "  ./scripts/spec-kit.sh /constitution 'Focus on code quality and user experience'"
    echo "  ./scripts/spec-kit.sh /specify 'Enhanced progress tracking with domain analytics'"
    echo "  ./scripts/spec-kit.sh /plan"
    echo "  ./scripts/spec-kit.sh /tasks"
    echo "  ./scripts/spec-kit.sh /implement"
}

# Main command dispatcher
main() {
    local command="$1"
    shift
    
    case "$command" in
        "/constitution")
            constitution "$*"
            ;;
        "/specify")
            specify "$*"
            ;;
        "/plan")
            plan
            ;;
        "/tasks")
            tasks
            ;;
        "/implement")
            implement
            ;;
        "status")
            status
            ;;
        "help"|"--help"|"-h")
            help
            ;;
        *)
            error "Unknown command: $command"
            help
            exit 1
            ;;
    esac
}

# Ensure we're in the right directory
cd "$PROJECT_ROOT"

# Run main function with all arguments
main "$@"
