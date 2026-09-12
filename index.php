<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NL2SQL - Intelligent Natural Language to SQL Engine</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-blue: #2563eb;
            --primary-dark: #1e40af;
            --bg-light: #f8fafc;
            --text-dark: #0f172a;
        }

        body {
            background-color: var(--bg-light);
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            color: #334155;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            margin: 0;
        }

        /* Navbar */
        .landing-nav {
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 1rem 1.5rem;
            width: 100%;
        }

        .brand-logo {
            font-weight: 700;
            font-size: 1.3rem;
            color: var(--text-dark);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .brand-logo i {
            color: var(--primary-blue);
        }

        /* Content Wrapper */
        .content-wrapper {
            max-width: 1200px;
            margin: 0 auto;
            width: 100%;
        }

        /* Hero Section */
        .hero-section {
            padding: 70px 0 45px;
            text-align: center;
        }

        .hero-badge {
            background: #eff6ff;
            color: var(--primary-blue);
            font-size: 0.85rem;
            font-weight: 600;
            padding: 6px 16px;
            border-radius: 9999px;
            display: inline-block;
            margin-bottom: 1.25rem;
            border: 1px solid #bfdbfe;
        }

        .hero-title {
            font-size: 3rem;
            font-weight: 800;
            color: var(--text-dark);
            letter-spacing: -0.02em;
            line-height: 1.2;
            margin: 0 auto 1.25rem;
        }

        .hero-title .highlight-text {
            color: var(--primary-blue);
        }

        .hero-desc {
            font-size: 1.15rem;
            color: #64748b;
            max-width: 750px;
            margin: 0 auto 2.25rem;
            line-height: 1.6;
        }

        .cta-group {
            display: flex;
            justify-content: center;
            gap: 14px;
            flex-wrap: wrap;
        }

        .btn-primary-custom {
            background-color: var(--primary-blue);
            color: white;
            padding: 12px 28px;
            font-weight: 600;
            font-size: 0.95rem;
            border-radius: 8px;
            border: none;
            text-decoration: none;
            box-shadow: 0 4px 14px rgba(37, 99, 235, 0.2);
            transition: all 0.2s;
        }

        .btn-primary-custom:hover {
            background-color: var(--primary-dark);
            color: white;
            transform: translateY(-2px);
        }

        .btn-outline-custom {
            background-color: #ffffff;
            color: #475569;
            padding: 12px 26px;
            font-weight: 600;
            font-size: 0.95rem;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            text-decoration: none;
            transition: all 0.2s;
        }

        .btn-outline-custom:hover {
            background-color: #f1f5f9;
            color: #1e293b;
        }

        /* Features Section */
        .features-section {
            padding: 20px 0 50px;
        }

        .feature-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 28px 24px;
            height: 100%;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .feature-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.04);
        }

        .feature-icon {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            background: #eff6ff;
            color: var(--primary-blue);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            margin-bottom: 18px;
        }

        .feature-title {
            font-weight: 700;
            font-size: 1.15rem;
            color: var(--text-dark);
            margin-bottom: 8px;
        }

        .feature-desc {
            font-size: 0.9rem;
            color: #64748b;
            line-height: 1.55;
            margin: 0;
        }

        /* How It Works Flow (Minimalist Timeline) */
        .how-section {
            padding: 40px 0 70px;
            border-top: 1px solid #e2e8f0;
        }

        .step-item {
            text-align: center;
            padding: 10px 15px;
        }

        .step-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 42px;
            height: 42px;
            background: #ffffff;
            color: var(--primary-blue);
            font-weight: 800;
            font-size: 1.1rem;
            border-radius: 50%;
            border: 2px solid var(--primary-blue);
            margin-bottom: 14px;
            box-shadow: 0 2px 6px rgba(37, 99, 235, 0.15);
        }

        /* Footer */
        footer {
            border-top: 1px solid #e2e8f0;
            background: #ffffff;
            padding: 20px 0;
            text-align: center;
            color: #94a3b8;
            font-size: 0.85rem;
            margin-top: auto;
        }

        /* =======================================================
   📱 FULL RESPONSIVE ENGINE (MOBILE & TABLETS)
======================================================= */
        @media (max-width: 992px) {

            /* 1. Pugngan ang horizontal overflow */
            html,
            body {
                overflow-x: hidden !important;
                width: 100% !important;
            }

            /* 2. Header & Navigation Adjustments */
            .landing-nav {
                padding: 0.75rem 1rem !important;
            }

            .brand-logo {
                font-size: 1.05rem !important;
            }

            .landing-nav .btn {
                font-size: 0.75rem !important;
                padding: 5px 10px !important;
                white-space: nowrap !important;
            }

            /* 3. Hero Section & Typography */
            .hero-section {
                padding: 40px 16px 30px !important;
            }

            .hero-title {
                font-size: 1.65rem !important;
                line-height: 1.25 !important;
            }

            .hero-desc {
                font-size: 0.9rem !important;
                padding: 0 10px !important;
            }

            /* 4. Showcase Workspace Container & Cards */
            .showcase-wrapper {
                margin: 30px auto !important;
                padding: 0 14px !important;
                max-width: 100% !important;
                box-sizing: border-box !important;
            }

            .showcase-card {
                padding: 14px !important;
            }

            /* I-convert ang 2-column grid ngadto sa 1 Column Stack */
            .showcase-grid {
                display: flex !important;
                flex-direction: column !important;
                gap: 16px !important;
            }

            .showcase-grid>div {
                width: 100% !important;
                max-width: 100% !important;
                box-sizing: border-box !important;
                min-width: 0 !important;
            }

            /* SQL Output & Text wrapping */
            .showcase-grid pre,
            .showcase-grid code {
                white-space: pre-wrap !important;
                word-break: break-word !important;
                font-size: 0.78rem !important;
            }

            /* 5. Footer Centering */
            footer {
                padding: 20px 14px !important;
            }

            footer p {
                font-size: 0.75rem !important;
                word-break: break-word !important;
            }
        }
    </style>
</head>

<body>

    <!-- Header Navigation -->
    <nav class="landing-nav">
        <div class="container-fluid px-3 px-md-4 d-flex justify-content-between align-items-center">
            <a href="index.php" class="brand-logo">
                <i class="fas fa-terminal"></i> NL2SQL Workspace
            </a>
            <div class="d-flex gap-2">
                <a href="dashboard.php" class="btn btn-outline-secondary px-3">Try Guest Mode</a>
                <a href="login.php" class="btn btn-primary px-3">Log In</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-section container-fluid px-4 content-wrapper">
        <span class="hero-badge"><i class="fas fa-microchip me-1"></i> NLP-Assisted Database Interface</span>
        <h1 class="hero-title">
            Translate Natural Language into<br>
            <span class="highlight-text">Structured SQL Queries</span>
        </h1>
        <p class="hero-desc">
            Bridge the gap between natural language database query requests and relational databases. Generate schema-aware, formatted queries across supported database dialects in real time.
        </p>
        <div class="cta-group">
            <a href="dashboard.php" class="btn-primary-custom">
                <i class="fas fa-play me-2"></i> Launch Workspace
            </a>
            <a href="login.php" class="btn-outline-custom">
                <i class="fas fa-user-plus me-2"></i> Register / Sign In
            </a>
        </div>
    </section>

    <!-- Core Features Section (3 Cards) -->
    <section class="features-section container-fluid px-4 content-wrapper">
        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-database"></i>
                    </div>
                    <h3 class="feature-title">Schema-Aware Context</h3>
                    <p class="feature-desc">
                        Upload custom `.sql` DDL schemas to enhance column mapping, foreign key associations, and relational structure recognition.
                    </p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-layer-group"></i>
                    </div>
                    <h3 class="feature-title">Multi-Dialect Support</h3>
                    <p class="feature-desc">
                        Target standard SQL syntax across MySQL/MariaDB, PostgreSQL, SQLite, and MS SQL Server without manual query restructuring.
                    </p>
                </div>
            </div>

            <div class="col-lg-4 col-md-12">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-project-diagram"></i>
                    </div>
                    <h3 class="feature-title">NLP Pipeline Inspection</h3>
                    <p class="feature-desc">
                        Inspect the AI-standardized input, detected SQL operation, extracted tables and columns, and pipeline validation status transparently.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- 🚀 AUTHENTIC WORKSPACE SHOWCASE -->
    <div class="showcase-wrapper" style="max-width: 1180px; margin: 60px auto 40px auto; padding: 0 20px;">

        <!-- SECTION TITLE -->
        <div style="text-align: center; margin-bottom: 30px;">
            <span style="background: #eff6ff; color: #2563eb; font-weight: 700; font-size: 0.8rem; padding: 4px 14px; border-radius: 20px; text-transform: uppercase; letter-spacing: 0.5px; border: 1px solid #bfdbfe;">
                Actual System Interface
            </span>
            <h2 style="font-size: 1.8rem; color: #0f172a; margin: 12px 0 6px 0; font-weight: 800;">
                See NL2SQL in Action
            </h2>
            <p style="color: #64748b; font-size: 0.95rem; margin: 0;">
                Real-time natural language to relational database query engine with schema intelligence.
            </p>
        </div>

        <!-- WORKSPACE PREVIEW WRAPPER (LIGHT BACKGROUND WITH SHADOW) -->
        <div class="showcase-card" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 16px; padding: 24px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.03);">
            <div class="showcase-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; align-items: stretch;">

                <!-- LEFT CARD: INPUT & SCHEMA -->
                <div style="background: white; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.04); display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                            <span style="font-weight: 700; color: #1e293b; font-size: 1rem;">Natural Language Input</span>
                            <span style="color: #94a3b8; font-size: 0.8rem; display: flex; align-items: center; gap: 4px;">
                                <i class="fas fa-trash-alt"></i> Clear
                            </span>
                        </div>

                        <div style="margin-bottom: 12px; display: flex; align-items: center; justify-content: space-between; background: #f8fafc; padding: 6px 12px; border-radius: 8px; border: 1px solid #e2e8f0; font-size: 0.82rem;">
                            <span style="font-weight: 600; color: #475569; display: flex; align-items: center; gap: 6px;">
                                <i class="fas fa-database" style="color: #2563eb;"></i> Target Dialect:
                            </span>
                            <span style="background: white; border: 1px solid #cbd5e1; padding: 3px 8px; border-radius: 6px; font-weight: 600; color: #1e293b;">
                                MySQL / MariaDB <i class="fas fa-chevron-down" style="font-size: 0.7rem; margin-left: 4px;"></i>
                            </span>
                        </div>

                        <div style="border: 1.5px solid #cbd5e1; border-radius: 8px; padding: 12px; min-height: 120px; font-size: 0.92rem; color: #1e293b; line-height: 1.5; background: #fff;">
                            Show the employee first name, last name, and the project names they are assigned to.
                        </div>

                        <button style="margin-top: 10px; width: 100%; background: #2563eb; color: white; border: none; padding: 10px; border-radius: 8px; font-weight: 600; font-size: 0.9rem; display: flex; align-items: center; justify-content: center; gap: 8px; cursor: default;">
                            <i class="fas fa-magic"></i> Generate SQL
                        </button>
                    </div>

                    <!-- SCHEMA ACTIVE BOX -->
                    <div style="margin-top: 14px; background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 8px; padding: 12px; text-align: center;">
                        <div style="font-size: 0.78rem; font-weight: 600; color: #475569; margin-bottom: 4px;">
                            <i class="fas fa-file-code" style="color: #2563eb;"></i> Optional Database Schema (.sql)
                        </div>
                        <div style="font-size: 0.72rem; color: #64748b; margin-bottom: 8px;">Enhance column & table recognition by uploading your structure.</div>
                        <div style="display: inline-flex; align-items: center; gap: 6px; background: #dcfce7; color: #15803d; padding: 3px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; border: 1px solid #86efac; margin-bottom: 6px;">
                            <i class="fas fa-check-circle"></i> Schema Active: <strong>company_management (1).sql</strong>
                        </div>
                        <div>
                            <span style="display: inline-block; background: #fee2e2; color: #dc2626; border: 1px solid #fca5a5; padding: 2px 8px; border-radius: 4px; font-size: 0.7rem; font-weight: 600;">
                                <i class="fas fa-trash-alt"></i> Remove Schema
                            </span>
                        </div>
                    </div>
                </div>

                <!-- RIGHT CARD: GENERATED SQL & EXPLANATION -->
                <div style="background: white; padding: 20px; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.04); display: flex; flex-direction: column; justify-content: space-between;">
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                            <h3 style="margin: 0; font-size: 1rem; color: #1e293b; font-weight: 700;">Generated SQL</h3>
                            <div style="display: flex; gap: 6px; color: #64748b; font-size: 0.8rem;">
                                <span style="border: 1px solid #e2e8f0; padding: 4px 8px; border-radius: 6px;"><i class="fas fa-pen-to-square"></i></span>
                                <span style="border: 1px solid #e2e8f0; padding: 4px 8px; border-radius: 6px;"><i class="far fa-copy"></i></span>
                                <span style="border: 1px solid #e2e8f0; padding: 4px 8px; border-radius: 6px;"><i class="fas fa-download"></i></span>
                            </div>
                        </div>

                        <!-- DARK SQL EDITOR BOX -->
                        <pre style="background: #0f172a; padding: 14px; border-radius: 8px; margin: 0; min-height: 140px; font-family: 'Consolas', monospace; font-size: 0.85rem; line-height: 1.6; color: #f8fafc; text-align: left; overflow-x: auto;"><code><span style="color: #f43f5e; font-weight: bold;">SELECT</span> e.first_name, e.last_name, p.project_name 
<span style="color: #f43f5e; font-weight: bold;">FROM</span> employees e 
<span style="color: #38bdf8; font-weight: bold;">JOIN</span> employee_projects ep <span style="color: #f43f5e; font-weight: bold;">ON</span> e.employee_id = ep.employee_id 
<span style="color: #38bdf8; font-weight: bold;">JOIN</span> projects p <span style="color: #f43f5e; font-weight: bold;">ON</span> ep.project_id = p.project_id;</code></pre>
                    </div>

                    <!-- QUERY INSIGHTS & EXPLANATION -->
                    <div style="margin-top: 14px; padding-top: 12px; border-top: 1px solid #f1f5f9;">
                        <div style="font-size: 0.85rem; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 6px; margin-bottom: 6px;">
                            <i class="fas fa-brain" style="color: #3b82f6;"></i> Query Insights & Explanation
                        </div>

                        <div style="font-size: 0.75rem; text-transform: uppercase; font-weight: 700; color: #1e293b; letter-spacing: 0.5px;">What it means (Explanation):</div>
                        <p style="margin: 4px 0 10px 0; font-size: 0.82rem; color: #334155; line-height: 1.5; background: #f8fafc; padding: 8px 10px; border-radius: 6px; border-left: 3px solid #3b82f6;">
                            This query retrieves records from the <strong>employees</strong> table with specific projected columns and custom aliases.
                        </p>

                        <!-- METRICS ROW -->
                        <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 10px; font-size: 0.78rem;">
                            <div>
                                <strong>Operation:</strong>
                                <span style="color: #2563eb; background: #eff6ff; padding: 2px 6px; border-radius: 4px; font-weight: 600;">JOIN</span>
                            </div>
                            <div>
                                <strong>Status:</strong>
                                <span style="color: #16a34a; font-weight: 600;">
                                    <i class="fas fa-check-circle"></i> Validated
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>

    <!-- How It Works Flow (Clean 3-Step Process Flow) -->
    <section class="how-section container-fluid px-4 content-wrapper">
        <div class="text-center mb-5">
            <h3 style="font-weight: 700; color: #0f172a; font-size: 1.45rem;">How It Works</h3>
            <p style="color: #64748b; font-size: 0.9rem;">From natural language prompt to executable query in seconds.</p>
        </div>

        <div class="row g-4">
            <div class="col-md-4">
                <div class="step-item">
                    <div class="step-pill">1</div>
                    <h4 style="font-weight: 700; font-size: 1.05rem; color: #1e293b; margin-bottom: 6px;">Input & Schema</h4>
                    <p style="color: #64748b; font-size: 0.88rem; line-height: 1.5; margin: 0;">
                        Type natural language database query requests and optionally bind your database `.sql` structure.
                    </p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="step-item">
                    <div class="step-pill">2</div>
                    <h4 style="font-weight: 700; font-size: 1.05rem; color: #1e293b; margin-bottom: 6px;">AI Pipeline Analysis</h4>
                    <p style="color: #64748b; font-size: 0.88rem; line-height: 1.5; margin: 0;">
                        The NLP engine maps schema entities and translates logic into your chosen dialect.
                    </p>
                </div>
            </div>

            <div class="col-md-4">
                <div class="step-item">
                    <div class="step-pill">3</div>
                    <h4 style="font-weight: 700; font-size: 1.05rem; color: #1e293b; margin-bottom: 6px;">Execute & Inspect</h4>
                    <p style="color: #64748b; font-size: 0.88rem; line-height: 1.5; margin: 0;">
                        Copy formatted queries, export results, and review explanation breakdowns.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container-fluid px-4 content-wrapper">
            <p class="mb-0">NL2SQL System &bull; Natural Language to SQL Query Interface</p>
        </div>
    </footer>

</body>

</html>