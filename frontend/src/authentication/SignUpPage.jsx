  import React from 'react';
import SignUpForm from '../components/SignUp';

function SignUpPage() {
  return (
    <div className="bg-gray-100 flex flex-col items-center justify-center h-screen">
      <h1 className="text-3xl font-bold mb-8">Create an account</h1>
      <div className="w-full max-w-md">
        <SignUpForm />
      </div>
    </div>
  );
}

export default SignUpPage;
