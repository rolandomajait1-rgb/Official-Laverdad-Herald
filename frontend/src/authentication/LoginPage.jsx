import React from 'react';
import Login from '../components/Login';

function LoginPage() {
  return (
    <div className="bg-gray-100 flex flex-col items-center justify-center h-screen">
      <h1 className="text-3xl font-bold mb-8">Login to your account</h1>
      <div className="w-full max-w-md">
        <Login/>
      </div>
    </div>
  );
}

export default LoginPage;
