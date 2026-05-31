export type Department = {
    id: number;
    name: string;
};

export type StudyProgram = {
    id: number;
    department_id: number;
    name: string;
    degree_level: string;
    department?: Department;
};

export type User = {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    must_change_password: boolean;
    role: 'ketua' | 'wakil_ketua' | 'sekretaris' | 'anggota' | null;
    academic_role: 'dosen' | 'mahasiswa' | null;
    entry_year: number | null;
    department_id: number | null;
    study_program_id: number | null;
    department?: Department;
    study_program?: StudyProgram;
    created_at: string;
    updated_at: string;
    [key: string]: unknown;
};

export type Auth = {
    user: User;
};

export type TwoFactorConfigContent = {
    title: string;
    description: string;
    buttonText: string;
};
