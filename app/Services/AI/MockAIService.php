<?php

namespace App\Services\AI;

class MockAIService implements AIServiceInterface
{
    /**
     * Parse raw resume text into structured candidate information dynamically.
     */
    public function parseResume(string $resumeText): array
    {
        // 1. Dynamic Contact Name Extraction (resilient to headers, ALL CAPS, and Title Case)
        $fullName = '';
        $cleanText = trim(preg_replace('/^(?:resume|curriculum vitae|cv|portfolio|profile)\s+/i', '', $resumeText));
        if (preg_match('/^([A-Z]{2,}(?:\s+[A-Z]{2,})+)/', $cleanText, $matches)) {
            $fullName = trim($matches[1]);
        } elseif (preg_match('/^([A-Z][a-z]+(?:\s+[A-Z][a-z]+)+)/', $cleanText, $matches)) {
            $fullName = trim($matches[1]);
        }

        if (empty($fullName)) {
            $lines = explode("\n", str_replace("\r", '', $resumeText));
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) {
                    continue;
                }
                if (preg_match('/^(?:resume|curriculum|cv|portfolio|page|profile|contact|email|phone|work|experience|education|skills|objective|about)/i', $line)) {
                    continue;
                }
                if (preg_match('/^[A-Z][a-zA-Z\s\.\,\-]+$/', $line) && strlen($line) < 40 && str_word_count($line) >= 2) {
                    $fullName = $line;
                    break;
                }
            }
        }

        if (empty($fullName)) {
            if (preg_match('/([A-Z][a-z]+ [A-Z][a-z]+)/', $resumeText, $matches)) {
                $fullName = $matches[1];
            } else {
                $fullName = null; // Let the caller fall back to application form name
            }
        }

        // 2. Dynamic Email Extraction
        $email = null;
        if (preg_match('/[a-z0-9\.\_\%\+\-]+@[a-z0-9\.\-]+\.[a-z]{2,4}/i', $resumeText, $matches)) {
            $email = trim($matches[0]);
        }

        // 3. Dynamic Phone Number Extraction
        $phone = null;
        if (preg_match('/(?:\+?\d{1,3}[-.\s]?)?\(?\d{3}\)?[-.\s]?\d{3}[-.\s]?\d{4}/', $resumeText, $matches)) {
            $phone = trim($matches[0]);
        } elseif (preg_match('/(?:\+91|0)?[789]\d{9}/', $resumeText, $matches)) {
            $phone = trim($matches[0]);
        }

        // 4. Dynamic Experience Level Analysis
        $totalExp = 0.0;
        if (stripos($resumeText, 'fresher') !== false || stripos($resumeText, 'freshers') !== false || stripos($resumeText, 'entry level') !== false || stripos($resumeText, 'no experience') !== false) {
            $totalExp = 0.0;
        } elseif (preg_match('/(\d+(?:\.\d+)?)\+?\s*(?:years?|yrs?)\s*(?:of\s*)?(?:exp|experience|work)/i', $resumeText, $matches)) {
            $totalExp = (float) $matches[1];
        } else {
            // Sum range differences
            preg_match_all('/(?:(?:Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[a-z]*\.?\s*)?((?:19|20)\d{2})\s*[-–—\/to\s]+(?:Present|Current|(?:(?:Jan|Feb|Mar|Apr|May|Jun|Jul|Aug|Sep|Oct|Nov|Dec)[a-z]*\.?\s*)?((?:19|20)\d{2}))/i', $resumeText, $rangeMatches, PREG_SET_ORDER);
            $durations = [];
            foreach ($rangeMatches as $match) {
                $startYear = intval($match[1]);
                $endYear = empty($match[2]) ? 2026 : intval($match[2]);
                $diff = $endYear - $startYear;
                if ($diff > 0 && $diff < 40) {
                    $durations[] = $diff;
                }
            }
            if (! empty($durations)) {
                $totalExp = (float) min(25, array_sum($durations));
            } else {
                // Infer based on first graduation year if present
                $year = 2026;
                if (preg_match('/(20\d{2})/', $resumeText, $yrMatches)) {
                    $passingYear = intval($yrMatches[1]);
                    if ($passingYear <= $year) {
                        $totalExp = (float) ($year - $passingYear);
                    }
                }
            }
        }
        $totalExp = max(0.0, $totalExp);

        // 5. Dynamic Location Extraction
        $locations = [
            'Bengaluru', 'Bangalore', 'Delhi', 'New Delhi', 'Noida', 'Gurugram', 'Gurgaon',
            'Mumbai', 'Bombay', 'Pune', 'Hyderabad', 'Chennai', 'Madras', 'Kolkata', 'Calcutta',
            'Ahmedabad', 'Jaipur', 'Chandigarh', 'Kochi', 'Cochin', 'Thiruvananthapuram', 'Trivandrum',
            'San Francisco', 'New York', 'Seattle', 'Austin', 'Boston', 'Chicago', 'Los Angeles',
            'London', 'Berlin', 'Munich', 'Paris', 'Amsterdam', 'Dublin', 'Singapore', 'Sydney',
            'Melbourne', 'Tokyo', 'Toronto', 'Vancouver',
        ];
        $location = 'Not Specified';
        if (preg_match('/([A-Z][a-zA-Z\s]+)\s*,\s*(India|USA|UK|United States|Germany|Canada|Australia|Singapore)/i', $resumeText, $matches)) {
            $location = trim($matches[1]).', '.trim($matches[2]);
        } else {
            foreach ($locations as $loc) {
                if (stripos($resumeText, $loc) !== false) {
                    $location = $loc;
                    break;
                }
            }
        }

        // 6. Dynamic Company & Designation Extraction
        $currentCompany = null;
        $currentDesignation = null;
        $roles = [
            'Laravel Developer', 'PHP Developer', 'Software Engineer', 'Backend Engineer',
            'Frontend Engineer', 'Full Stack Developer', 'Full-Stack Developer', 'Lead Developer',
            'Senior Developer', 'Technical Lead', 'Architect', 'Product Manager', 'Data Scientist',
            'DevOps Engineer', 'System Administrator', 'QA Engineer', 'Mobile Developer',
            'Android Developer', 'iOS Developer', 'UI/UX Designer', 'Intern', 'Fresher',
        ];

        foreach ($roles as $role) {
            if (preg_match('/\b'.preg_quote($role, '/').'\b/i', $resumeText, $matches)) {
                $currentDesignation = $matches[0];
                break;
            }
        }

        // Find company globally by searching for names ending with standard company suffixes
        $cleanText = preg_replace('/\s+/', ' ', $resumeText);
        if (preg_match('/((?:[A-Z][a-zA-Z0-9\&\.\-]*\s+){1,4}\b(?:Technologies|Solutions|Inc|LLC|Corp|Software|Systems|Tech|Lab|Labs|Consulting|Services|Group|Pvt\s*Ltd|Pvt\.\s*Ltd\.|Ltd)\b)/', $cleanText, $coMatches)) {
            $currentCompany = trim($coMatches[1]);
            $prefixesPattern = '/^(?:Job|Backend|Frontend|Fullstack|Full-Stack|Software|Lead|Senior|Junior|Developer|Engineer|Architect|Manager|Director|Analyst|Designer|Fresh|Fresher|Intern|at|with|for)\b\s*/i';
            while (preg_match($prefixesPattern, $currentCompany)) {
                $currentCompany = preg_replace($prefixesPattern, '', $currentCompany);
            }
        }

        // If still empty, try matching "Designation at/with/for Company" structure
        if (empty($currentCompany) && ! empty($currentDesignation)) {
            $pos = stripos($resumeText, $currentDesignation);
            $subText = substr($resumeText, $pos, 150);
            if (preg_match('/(?:at|with|for)\s+([A-Z][a-zA-Z0-9\s\&]{3,25})/i', $subText, $coMatches)) {
                $currentCompany = trim($coMatches[1]);
            }
        }

        if ($totalExp == 0.0) {
            $currentCompany = $currentCompany ?? 'None (Fresher)';
            $currentDesignation = $currentDesignation ?? 'Candidate (Fresher)';
        } else {
            $currentCompany = $currentCompany ?? 'Freelance / Self-Employed';
            $currentDesignation = $currentDesignation ?? 'Software Professional';
        }

        // 7. Dynamic Skills Extraction
        $techCatalog = [
            'PHP', 'Laravel', 'Symfony', 'Yii', 'CodeIgniter', 'Zend', 'CakePHP', 'WordPress', 'Magento', 'Drupal',
            'JavaScript', 'TypeScript', 'Node.js', 'Express', 'React', 'React.js', 'Vue', 'Vue.js', 'Angular', 'Next.js',
            'Nuxt.js', 'NestJS', 'Svelte', 'jQuery', 'HTML', 'CSS', 'Sass', 'SCSS', 'Less', 'Tailwind CSS', 'Bootstrap',
            'Python', 'Django', 'Flask', 'FastAPI', 'Pandas', 'NumPy', 'Java', 'Spring Boot', 'Spring', 'Hibernate',
            'C#', '.NET', 'ASP.NET', 'C++', 'C', 'Go', 'Golang', 'Rust', 'Ruby', 'Rails', 'Ruby on Rails', 'Scala', 'Kotlin', 'Swift',
            'SQL', 'MySQL', 'PostgreSQL', 'SQLite', 'MongoDB', 'Redis', 'Memcached', 'Elasticsearch', 'Oracle', 'SQL Server', 'MariaDB',
            'Cassandra', 'DynamoDB', 'Firebase', 'GraphQL', 'REST APIs', 'RESTful APIs', 'gRPC', 'SOAP', 'WebSockets',
            'Docker', 'Kubernetes', 'AWS', 'Amazon Web Services', 'Azure', 'GCP', 'Google Cloud', 'Terraform', 'Ansible', 'Jenkins',
            'CI/CD', 'GitHub Actions', 'Git', 'GitHub', 'GitLab', 'Bitbucket', 'Linux', 'Apache', 'Nginx', 'IIS',
            'Microservices', 'Serverless', 'OAuth', 'JWT', 'Unit Testing', 'PHPUnit', 'Pest', 'Jest', 'Cypress', 'Selenium',
            'Agile', 'Scrum', 'Jira', 'Confluence', 'Redux', 'Vuex', 'Pinia', 'Webpack', 'Vite', 'Babel', 'NPM', 'Composer',
        ];

        $softCatalog = [
            'Communication', 'Problem Solving', 'Teamwork', 'Collaboration', 'Leadership', 'Time Management', 'Critical Thinking',
            'Adaptability', 'Creativity', 'Conflict Resolution', 'Emotional Intelligence', 'Decision Making', 'Interpersonal Skills',
            'Work Ethic', 'Attention to Detail', 'Mentorship', 'Agile Mindset',
        ];

        $parsedSkills = [];
        foreach ($techCatalog as $skill) {
            $pattern = '/\b'.preg_quote($skill, '/').'\b/i';
            if ($skill === 'Go' || $skill === 'C') {
                $pattern = '/\b'.preg_quote($skill, '/').'\b/s';
            }
            if (preg_match($pattern, $resumeText)) {
                $parsedSkills[] = ['name' => $skill, 'type' => 'technical'];
            }
        }
        foreach ($softCatalog as $skill) {
            if (preg_match('/\b'.preg_quote($skill, '/').'\b/i', $resumeText)) {
                $parsedSkills[] = ['name' => $skill, 'type' => 'soft'];
            }
        }

        if (empty($parsedSkills)) {
            $parsedSkills = [
                ['name' => 'Software Development', 'type' => 'technical'],
                ['name' => 'Communication', 'type' => 'soft'],
                ['name' => 'Problem Solving', 'type' => 'soft'],
            ];
        }

        // 8. Dynamic Education Extraction
        $education = [];
        $eduKeywords = ['B.Tech', 'Bachelor of Technology', 'M.Tech', 'Master of Technology', 'B.E', 'Bachelor of Engineering', 'B.Sc', 'Bachelor of Science', 'MCA', 'Master of Computer Applications', 'BCA', 'Bachelor of Computer Applications', 'MBA', 'Master of Business Administration', 'Ph.D', 'Doctor of Philosophy', 'Bachelor', 'Master'];

        foreach ($eduKeywords as $eduKey) {
            $pos = stripos($resumeText, $eduKey);
            if ($pos !== false) {
                $subText = substr($resumeText, max(0, $pos - 100), 300);
                $college = null;
                if (preg_match('/([A-Za-z0-9\s\,\&]+(?:University|Institute|College|Academy|School))/i', $subText, $collMatches)) {
                    $college = trim($collMatches[1]);
                }
                $year = null;
                if (preg_match('/\b(20\d{2}|19\d{2})\b/', $subText, $yrMatches)) {
                    $year = intval($yrMatches[1]);
                }

                $education[] = [
                    'degree' => $eduKey,
                    'college' => $college ?? 'Not Specified',
                    'passing_year' => $year ?? 2020,
                ];

                if (count($education) >= 2) {
                    break;
                }
            }
        }

        if (empty($education)) {
            if (preg_match('/([A-Za-z0-9\s\,\&]+(?:University|Institute|College|Academy|School))/i', $resumeText, $collMatches)) {
                $education[] = [
                    'degree' => 'Bachelor Degree',
                    'college' => trim($collMatches[1]),
                    'passing_year' => 2020,
                ];
            } else {
                $education[] = [
                    'degree' => 'Bachelor Degree',
                    'college' => 'Not Specified',
                    'passing_year' => 2020,
                ];
            }
        }

        // 9. Dynamic Projects Extraction
        $projects = [];
        $projPos = stripos($resumeText, 'PROJECTS');
        if ($projPos === false) {
            $projPos = stripos($resumeText, 'Projects');
        }
        if ($projPos === false) {
            $projPos = stripos($resumeText, 'Key Project');
        }

        if ($projPos !== false) {
            $projSection = substr($resumeText, $projPos + 8, 800);
            $projLines = preg_split('/[\n\•]/', $projSection);
            foreach ($projLines as $line) {
                $line = trim($line);
                if (strlen($line) > 20 && strlen($line) < 150) {
                    if (preg_match('/^[A-Z][A-Za-z0-9\s\-\:]+(?:System|App|Application|Platform|Portal|Website|Tool|Framework|Engine|Service|Database|Manager|Site)/i', $line, $projMatches)) {
                        $projName = trim($projMatches[0]);
                        $techUsed = [];
                        foreach ($techCatalog as $tech) {
                            if (stripos($line, $tech) !== false) {
                                $techUsed[] = $tech;
                            }
                        }
                        $projects[] = [
                            'name' => $projName,
                            'technologies_used' => ! empty($techUsed) ? implode(', ', array_slice($techUsed, 0, 4)) : 'Software Stack',
                        ];
                        if (count($projects) >= 3) {
                            break;
                        }
                    }
                }
            }
        }

        if (empty($projects)) {
            $techNames = array_column(array_filter($parsedSkills, fn ($s) => $s['type'] === 'technical'), 'name');
            $techList = implode(', ', array_slice($techNames, 0, 4));

            if ($totalExp == 0.0) {
                $projects[] = [
                    'name' => 'Academic Portfolio Showcase',
                    'technologies_used' => $techList ?: 'HTML, CSS, JavaScript',
                ];
            } else {
                $projects[] = [
                    'name' => 'Enterprise System Integration',
                    'technologies_used' => $techList ?: 'PHP, Laravel, MySQL',
                ];
            }
        }

        return [
            'full_name' => $fullName,
            'email' => $email,
            'phone' => $phone,
            'location' => $location,
            'linkedin_url' => $fullName ? 'https://linkedin.com/in/'.strtolower(str_replace(' ', '', $fullName)) : null,
            'portfolio_url' => $fullName ? 'https://'.strtolower(str_replace(' ', '', $fullName)).'.dev' : null,
            'total_experience_years' => $totalExp,
            'current_company' => $currentCompany,
            'current_designation' => $currentDesignation,
            'skills' => $parsedSkills,
            'education' => $education,
            'projects' => $projects,
        ];
    }

    /**
     * Compare parsed resume data against job requirements to calculate scores & recommendations.
     */
    public function screenCandidate(array $parsedResume, array $jobRequirement): array
    {
        $jobTitle = $jobRequirement['title'] ?? 'Lead Developer';
        $jobDesc = strtolower($jobRequirement['description'] ?? '');
        $skills = $parsedResume['skills'] ?? [];
        $education = $parsedResume['education'] ?? [];
        $projects = $parsedResume['projects'] ?? [];

        // 1. Dynamic extraction of required skills from the Job post description
        $techCatalog = [
            'PHP', 'Laravel', 'Symfony', 'CodeIgniter', 'WordPress',
            'JavaScript', 'TypeScript', 'Node.js', 'Express', 'React', 'Vue', 'Angular', 'Next.js',
            'Python', 'Django', 'Flask', 'FastAPI', 'Java', 'Spring Boot', 'C#', '.NET',
            'C++', 'Go', 'Golang', 'Rust', 'Ruby', 'Rails',
            'HTML', 'CSS', 'Tailwind CSS', 'Bootstrap',
            'MySQL', 'PostgreSQL', 'SQLite', 'MongoDB', 'Redis', 'Elasticsearch',
            'Docker', 'Kubernetes', 'AWS', 'Azure', 'GCP', 'Terraform', 'Jenkins', 'CI/CD',
            'REST APIs', 'GraphQL', 'Git', 'GitHub', 'Agile', 'Scrum',
        ];

        $requiredSkills = [];
        $jobTitleAndDesc = $jobTitle.' '.$jobDesc;
        foreach ($techCatalog as $tech) {
            if (stripos($jobTitleAndDesc, $tech) !== false) {
                $requiredSkills[] = $tech;
            }
        }
        if (empty($requiredSkills)) {
            $requiredSkills = ['PHP', 'Laravel', 'REST APIs', 'MySQL'];
        }

        // 2. Identify Matched and Missing Skills
        $candidateSkillsList = array_map(fn ($s) => $s['name'], $skills);
        $matched = [];
        $missing = [];
        foreach ($requiredSkills as $req) {
            $found = false;
            foreach ($candidateSkillsList as $candSkill) {
                if (strtolower($candSkill) === strtolower($req)) {
                    $found = true;
                    break;
                }
            }
            if ($found) {
                $matched[] = $req;
            } else {
                $missing[] = $req;
            }
        }

        if (empty($matched)) {
            $matched = ! empty($candidateSkillsList) ? array_slice($candidateSkillsList, 0, 4) : ['Software Development'];
        }

        // 3. Compute Scores dynamically
        // Skills Match Score
        $skillsScore = count($requiredSkills) > 0 ? intval((count($matched) / count($requiredSkills)) * 100) : 100;
        $skillsScore = max(30, min(100, $skillsScore));

        // Experience Score
        $reqExp = 0.0;
        if (preg_match('/(\d+(?:\.\d+)?)/', $jobRequirement['experience_required'] ?? '', $expMatches)) {
            $reqExp = (float) $expMatches[1];
        }
        $candExp = floatval($parsedResume['total_experience_years'] ?? 0.0);
        if ($reqExp == 0.0) {
            $experienceScore = 100;
        } else {
            $experienceScore = $candExp >= $reqExp ? 100 : intval(($candExp / $reqExp) * 100);
        }
        $experienceScore = max(30, min(100, $experienceScore));

        // Project Relevance Score
        $projectScore = 0;
        if (! empty($projects)) {
            $projScores = [];
            foreach ($projects as $proj) {
                $projTech = $proj['technologies_used'] ?? '';
                $projMatchCount = 0;
                foreach ($requiredSkills as $req) {
                    if (stripos($projTech, $req) !== false) {
                        $projMatchCount++;
                    }
                }
                $projScores[] = count($requiredSkills) > 0 ? ($projMatchCount / count($requiredSkills)) * 100 : 100;
            }
            $projectScore = intval(array_sum($projScores) / count($projects));
            $projectScore = max(50, min(100, $projectScore + 20));
        } else {
            $projectScore = 40;
        }

        // Educational Alignment Score
        $educationScore = 50;
        $degree = ! empty($education) ? ($education[0]['degree'] ?? '') : 'Degree';
        if (! empty($education)) {
            $degreeLower = strtolower($degree);
            if (str_contains($degreeLower, 'computer') || str_contains($degreeLower, 'technology') || str_contains($degreeLower, 'engineering') || str_contains($degreeLower, 'b.tech') || str_contains($degreeLower, 'm.tech') || str_contains($degreeLower, 'mca') || str_contains($degreeLower, 'bca') || str_contains($degreeLower, 'b.e') || str_contains($degreeLower, 'bscs')) {
                $educationScore = 95;
            } elseif (str_contains($degreeLower, 'science') || str_contains($degreeLower, 'math') || str_contains($degreeLower, 'physics')) {
                $educationScore = 85;
            } else {
                $educationScore = 75;
            }
        }

        // Weighted Heuristic Match Score (Skills 40%, Experience 30%, Projects 15%, Education 15%)
        $matchScore = intval(($skillsScore * 0.4) + ($experienceScore * 0.3) + ($projectScore * 0.15) + ($educationScore * 0.15));
        $matchScore = max(30, min(100, $matchScore));

        $recommendation = 'Hold for Review';
        if ($matchScore >= 90) {
            $recommendation = 'Strongly Recommended';
        } elseif ($matchScore >= 75) {
            $recommendation = 'Recommended for Interview';
        } elseif ($matchScore < 60) {
            $recommendation = 'Do Not Hire';
        }

        $experienceGap = "Candidate's experience level of {$candExp} years aligns perfectly with or exceeds the job requirement of {$reqExp}+ years.";
        if ($candExp < $reqExp) {
            $experienceGap = "Candidate has {$candExp} years of experience, which is below the target job requirement of {$reqExp}+ years.";
        }

        $candName = $parsedResume['full_name'] ?? 'Candidate';
        $currentDesignation = $parsedResume['current_designation'] ?? 'Software Professional';
        $currentCompany = $parsedResume['current_company'] ?? 'Freelance / Self-Employed';

        // 100% Dynamic evaluation summary
        $analysisSummary = "Candidate '{$candName}' exhibits a suitability rating of {$matchScore}% for the '{$jobTitle}' position. With a total of {$candExp} years of hands-on experience, currently operating as a '{$currentDesignation}' at '{$currentCompany}', they demonstrate proficiency in ".implode(', ', array_slice($matched, 0, 4)).'. ';
        if (! empty($missing)) {
            $analysisSummary .= 'The primary areas of alignment gaps are in '.implode(', ', array_slice($missing, 0, 3)).", but their academic background in '{$degree}' provides a solid baseline.";
        } else {
            $analysisSummary .= 'They show an exceptional technical match with zero skill gaps identified across all core parameters.';
        }

        return [
            'match_score' => $matchScore,
            'analysis_summary' => $analysisSummary,
            'strengths' => $matched,
            'missing_skills' => $missing,
            'experience_gap' => $experienceGap,
            'hiring_recommendation' => $recommendation,
            'evaluation_scorecard' => [
                'skills_evaluation' => [
                    'score' => $skillsScore,
                    'feedback' => 'Demonstrates capabilities in: '.implode(', ', array_slice($matched, 0, 4)).'.',
                ],
                'experience_evaluation' => [
                    'score' => $experienceScore,
                    'feedback' => "Professional background displays {$candExp} years of coding history.",
                ],
                'project_relevance' => [
                    'score' => $projectScore,
                    'feedback' => 'Projects show appropriate architecture design matching job descriptions.',
                ],
                'educational_alignment' => [
                    'score' => $educationScore,
                    'feedback' => "Academic degree '{$degree}' matches technical capabilities.",
                ],
            ],
            'feedback_form' => [
                'strengths_summary' => 'Solid technical portfolio in '.implode(', ', array_slice($matched, 0, 3)).', clean documentation, architectural understanding.',
                'weaknesses_summary' => ! empty($missing) ? 'Slight skill deficit in '.implode(', ', array_slice($missing, 0, 3)).'.' : 'None identified.',
                'overall_fit' => 'Highly aligned for core product engineering requirements.',
            ],
        ];
    }

    /**
     * Generate customized dynamic interview questions based on candidate profile and job requirements.
     */
    public function generateInterviewQuestions(array $parsedResume, array $jobRequirement): array
    {
        $fullName = $parsedResume['full_name'] ?? 'the candidate';
        $techSkills = array_filter($parsedResume['skills'] ?? [], fn ($s) => $s['type'] === 'technical');
        $techNames = array_column($techSkills, 'name');

        $education = $parsedResume['education'] ?? [];
        $degree = ! empty($education) ? $education[0]['degree'] : 'Computer Science';
        $college = ! empty($education) ? $education[0]['college'] : 'University';

        $projects = $parsedResume['projects'] ?? [];
        $project1 = ! empty($projects) ? $projects[0]['name'] : 'Enterprise Automation System';
        $project1Tech = ! empty($projects) ? $projects[0]['technologies_used'] : 'PHP, MySQL';

        $company = $parsedResume['current_company'] ?? 'Freelance / Self-Employed';
        $desig = $parsedResume['current_designation'] ?? 'Software Professional';
        $exp = floatval($parsedResume['total_experience_years'] ?? 3.0);

        // Dynamic match calculation for missing skills
        $jobTitle = $jobRequirement['title'] ?? 'Lead Developer';
        $jobDesc = strtolower($jobRequirement['description'] ?? '');
        $missing = [];
        $techKeywords = ['laravel', 'php', 'mysql', 'docker', 'aws', 'kubernetes', 'vue', 'react', 'typescript', 'node.js', 'python', 'django', 'postgresql', 'redis', 'graphql', 'html', 'css', 'javascript', 'tailwind css', 'git', 'java', 'spring boot'];
        foreach ($techKeywords as $kw) {
            if (str_contains($jobDesc, $kw) || str_contains(strtolower($jobTitle), $kw)) {
                $hasInResume = false;
                foreach ($parsedResume['skills'] ?? [] as $s) {
                    if (strtolower($s['name']) === $kw) {
                        $hasInResume = true;
                        break;
                    }
                }
                if (! $hasInResume) {
                    $missing[] = ucfirst($kw);
                }
            }
        }
        if (empty($missing)) {
            $missing = ['AWS', 'Kubernetes'];
        }

        $questions = [];

        // Question 1: Dynamic Educational Question
        $questions[] = [
            'question' => "In your resume, you listed a '{$degree}' from '{$college}'. How has your academic background prepared you for the complexities of a senior role like '{$jobTitle}'?",
            'category' => 'educational',
            'difficulty' => 'easy',
            'suggested_answer' => "The candidate should connect theoretical computer science concepts (algorithms, databases, software engineering principles) learned at '{$college}' with practical, real-world application architectures.",
        ];

        // Question 2: Dynamic Project Question
        $questions[] = [
            'question' => "You worked on the project '{$project1}' utilizing '{$project1Tech}'. What were the biggest architectural challenges you faced during this project, and how did you resolve them?",
            'category' => 'technical',
            'difficulty' => 'hard',
            'suggested_answer' => "Should explain high-level system design details, scalability bottlenecks (e.g. database query count, slow API response), and how they used '{$project1Tech}' features to optimize the system.",
        ];

        // Question 3: Dynamic Technical Question
        $primarySkill = ! empty($techNames) ? $techNames[0] : 'PHP';
        $secondarySkill = count($techNames) > 1 ? $techNames[1] : 'Laravel';
        $questions[] = [
            'question' => "Since you possess strong expertise in '{$primarySkill}' and '{$secondarySkill}', can you explain an advanced production scenario where you had to perform deep performance tuning or debug memory leaks using these technologies?",
            'category' => 'technical',
            'difficulty' => 'hard',
            'suggested_answer' => 'Candidate should describe profiling tools (like Xdebug, Blackfire, React DevTools), memory leak detection, optimization of garbage collection, database connection pools, or lazy-loading.',
        ];

        // Question 4: Dynamic Missing Skills Question
        $missingSkill = $missing[0];
        $questions[] = [
            'question' => "The role of '{$jobTitle}' highly prioritizes hands-on experience with '{$missingSkill}'. Your profile doesn't explicitly highlight this skill. How would you leverage your current expertise in ".implode(', ', array_slice($techNames, 0, 3))." to quickly get up to speed with '{$missingSkill}'?",
            'category' => 'scenario',
            'difficulty' => 'medium',
            'suggested_answer' => "The candidate should display strong learning agility, show how their existing skills map to '{$missingSkill}' (e.g. mapping relational DB concepts to NoSQL, or general cloud scaling patterns), and discuss past successful tool migrations.",
        ];

        // Question 5: Dynamic Behavioral & Leadership Question
        $questions[] = [
            'question' => "Having spent {$exp} years in the industry, and currently working as a '{$desig}' at '{$company}', how do you manage technical debt and ensure code quality guidelines within your team when building rapid feature iterations?",
            'category' => 'behavioral',
            'difficulty' => 'medium',
            'suggested_answer' => 'Should discuss code reviews, unit testing standards, automated CI/CD pipelines, architectural decision logs (ADRs), and balance between speed-to-market and long-term codebase maintainability.',
        ];

        return $questions;
    }
}
