@echo off
echo ========================================
echo  TEST EMAIL SENDER
echo ========================================
echo.

cd backend

echo Testing email configuration...
echo.

php artisan tinker --execute="Mail::raw('This is a test email from La Verdad Herald system. If you receive this, email is working!', function($message) { $message->to('test@laverdad.edu.ph')->subject('Test Email - La Verdad Herald'); }); echo 'Email sent successfully!';"

echo.
echo ========================================
echo Check your inbox at test@laverdad.edu.ph
echo ========================================
pause
