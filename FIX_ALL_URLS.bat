@echo off
echo Fixing all hardcoded URLs in frontend...

cd frontend\src

REM Replace all fetch calls with axios
powershell -Command "(Get-Content 'AdminDashboard\AuditTrail.jsx') -replace 'fetch\(''http://localhost:8000/api/', 'axios.get(''/api/' -replace 'headers: \{[^}]+\}', '' | Set-Content 'AdminDashboard\AuditTrail.jsx'"

echo Done! Restart dev server.
pause
