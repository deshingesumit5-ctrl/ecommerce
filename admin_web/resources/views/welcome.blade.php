<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel Web</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }
        body {
            background: #0f172a;
            color: #f8fafc;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .container {
            max-width: 720px;
            width: 100%;
            background: #1e293b;
            border: 1px solid #334155;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            text-align: center;
        }
        .badge {
            display: inline-block;
            background: rgba(99, 102, 241, 0.15);
            color: #818cf8;
            border: 1px solid rgba(99, 102, 241, 0.3);
            padding: 6px 16px;
            border-radius: 9999px;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 20px;
        }
        h1 {
            font-size: 32px;
            font-weight: 800;
            color: #ffffff;
            margin-bottom: 12px;
        }
        p {
            color: #94a3b8;
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 28px;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 16px;
            margin-bottom: 32px;
            text-align: left;
        }
        .card {
            background: #0f172a;
            padding: 16px 20px;
            border-radius: 14px;
            border: 1px solid #334155;
        }
        .card-title {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
            font-weight: 700;
            margin-bottom: 6px;
        }
        .card-value {
            font-size: 15px;
            font-weight: 600;
            color: #38bdf8;
            word-break: break-all;
        }
        .status-dot {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #22c55e;
            margin-right: 6px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="badge"><span class="status-dot"></span>Laravel {{ app()->version() }} • PHP {{ phpversion() }}</div>
        <h1>Admin Web</h1>
        <p>Your PHP Laravel administration project is successfully set up and connected to the MySQL <strong>admin_web</strong> database.</p>
        
        <div class="grid">
            <div class="card">
                <div class="card-title">Project Name</div>
                <div class="card-value">admin_web</div>
            </div>
            <div class="card">
                <div class="card-title">Database</div>
                <div class="card-value">{{ config('database.connections.mysql.database') }}</div>
            </div>
            <div class="card">
                <div class="card-title">DB Host</div>
                <div class="card-value">{{ config('database.connections.mysql.host') }}:{{ config('database.connections.mysql.port') }}</div>
            </div>
        </div>
    </div>
</body>
</html>
