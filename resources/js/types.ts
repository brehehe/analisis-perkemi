import type { PageProps } from '@inertiajs/core';

export type AuthUser = {
    id: number;
    name: string;
    email: string;
    roles: string[];
    permissions: string[];
};

export type SharedPageProps = PageProps & {
    auth: { user: AuthUser | null };
    flash: { success: string | null; error: string | null };
};

export type Option = { value: string; label: string };

export type PaginatorLink = {
    url: string | null;
    label: string;
    active: boolean;
};

export type Paginated<T> = {
    data: T[];
    current_page: number;
    from: number | null;
    last_page: number;
    links: PaginatorLink[];
    per_page: number;
    to: number | null;
    total: number;
};

export type Club = { id: string; name: string; city?: string | null };
export type Coach = { id: string; name: string; email?: string | null; club_id?: string | null };

export type Athlete = {
    id: string;
    identifier: string;
    name: string;
    gender: 'male' | 'female';
    date_of_birth?: string | null;
    category: string;
    weight_class?: string | null;
    experience_years?: number;
    status: 'active' | 'inactive';
    club_id?: string | null;
    coach_id?: string | null;
    club?: Club | null;
    coach?: Coach | null;
    matches_count?: number;
    analyses_count?: number;
    matches?: MatchRecord[];
};

export type CompetitionEvent = {
    id: string;
    name: string;
    starts_at?: string;
    venue?: string | null;
    city?: string | null;
};

export type MatchRecord = {
    id: string;
    athlete_id: string;
    competition_event_id?: string | null;
    opponent_name?: string | null;
    opponent_club?: string | null;
    match_date: string;
    category: string;
    match_type?: string;
    division?: 'male' | 'female' | 'mixed' | null;
    result: string;
    athlete_score?: number | null;
    opponent_score?: number | null;
    status: string;
    notes?: string | null;
    athlete?: Athlete;
    athletes?: Athlete[];
    competition_event?: CompetitionEvent | null;
    videos_count?: number;
    analyses_count?: number;
    videos?: Video[];
    analyses?: Analysis[];
};

export type Video = {
    id: string;
    source: 'upload' | 'youtube';
    original_name?: string | null;
    external_url?: string | null;
    focus_description?: string | null;
    processing_status: string;
    duration_seconds?: number | null;
};

export type AnalysisMetric = {
    id: string;
    category: string;
    name: string;
    score: string;
    evidence?: { observations?: string[] } | null;
};

export type AnalysisEvent = {
    id: string;
    event_type: string;
    occurred_at_ms: number;
    confidence: string;
    title: string;
    description: string;
    evidence?: { frame_index?: number; observations?: string[] } | null;
    validation_status: string;
};

export type PointOpportunity = {
    id: string;
    opportunity_type: string;
    occurred_at_ms: number;
    confidence: string;
    trigger: string;
    explanation: string;
    recommended_action?: string | null;
    evidence?: { frame_index?: number; observations?: string[] } | null;
    validation_status: string;
};

export type AnalysisFinding = {
    title: string;
    detail: string;
    confidence: number;
    evidence: string[];
};

export type AnalysisProfileItem = {
    aspect: string;
    assessment: string;
    confidence: number;
    evidence: string[];
};

export type MatchIntelligence = {
    executive_summary: string;
    analysis_scope: string;
    frames_analyzed: number;
    target_identification: {
        label: string;
        basis: string;
        confidence: number;
        caveat: string;
    };
    strengths: AnalysisFinding[];
    weaknesses: AnalysisFinding[];
    athlete_profile: AnalysisProfileItem[];
    opponent_profile: AnalysisProfileItem[];
    match_dynamics: Array<{
        phase: string;
        start_timestamp_seconds: number;
        end_timestamp_seconds: number;
        momentum: string;
        athlete_actions: string[];
        opponent_actions: string[];
        coaching_note: string;
    }>;
    risk_flags: Array<{
        risk: string;
        impact: string;
        mitigation: string;
        timestamp_seconds: number | null;
        confidence: number;
        evidence: string[];
    }>;
    limitations: string[];
};

export type Strategy = {
    summary: string;
    attack_strategy: string;
    counter_strategy: string;
    defensive_strategy: string;
    what_to_avoid: string;
    priority_points?: string[];
};

export type TrainingRecommendation = {
    id: string;
    priority: 'high' | 'medium' | 'low';
    drill: string;
    frequency?: string | null;
    duration_minutes?: number | null;
    target_metric?: string | null;
    target_score?: string | null;
};

export type Analysis = {
    id: string;
    match_record_id: string;
    video_id: string;
    athlete_id: string;
    status: string;
    progress: number;
    current_step?: string | null;
    model_version?: string | null;
    overall_score?: string | null;
    error_message?: string | null;
    created_at?: string;
    athlete?: Athlete;
    match_record?: MatchRecord;
    video?: Video;
    match_intelligence?: MatchIntelligence | null;
    metrics?: AnalysisMetric[];
    events?: AnalysisEvent[];
    opportunities?: PointOpportunity[];
    latest_strategy?: Strategy | null;
    training_recommendations?: TrainingRecommendation[];
    coach_report?: Record<string, unknown> | null;
};
