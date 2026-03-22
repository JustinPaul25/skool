import { z } from 'zod';

export const personalSchema = z.object({
    first_name: z.string().trim().min(1, 'First name is required').max(255),
    last_name: z.string().trim().min(1, 'Last name is required').max(255),
    middle_name: z.string().trim().max(255).optional(),
    birth_date: z.string().min(1, 'Birth date is required'),
    gender: z.enum(['male', 'female'], { message: 'Select a gender' }),
    address: z.string().trim().max(1000).optional(),
});

export const guardianSchema = z.object({
    guardian_name: z.string().trim().min(1, 'Guardian name is required').max(255),
    guardian_phone: z.string().trim().min(1, 'Guardian phone is required').max(50),
    guardian_relationship: z.string().trim().max(100).optional(),
    phone: z.string().trim().max(50).optional(),
    email: z
        .union([z.literal(''), z.string().trim().email('Enter a valid email')])
        .optional(),
});

export const academicSchema = z.object({
    branch_id: z.coerce.number().int().positive('Select a branch'),
    grade_level_id: z.coerce.number().int().positive('Select a grade level'),
    school_year_id: z.coerce.number().int().positive(),
    notes: z.string().trim().max(2000).optional(),
});

export type PersonalValues = z.infer<typeof personalSchema>;
export type GuardianValues = z.infer<typeof guardianSchema>;
export type AcademicValues = z.infer<typeof academicSchema>;
