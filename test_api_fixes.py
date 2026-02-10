#!/usr/bin/env python3
"""
Complete API Endpoint Testing Script
Senior Developer Debug Session
"""

import requests
import json
import sys

BASE_URL = "http://localhost:8000/api"

def test_endpoints():
    print("\n" + "="*80)
    print("DEBUGGING LA VERDAD HERALD - API ENDPOINT FIXES")
    print("="*80 + "\n")
    
    # Auth first
    print("[PHASE 1] AUTHENTICATION")
    print("-" * 80)
    try:
        login_resp = requests.post(
            f"{BASE_URL}/login",
            json={"email": "final@test.com", "password": "Pass123"}
        )
        if login_resp.status_code == 200:
            token = login_resp.json()["token"]
            headers = {"Authorization": f"Bearer {token}"}
            print(f"✓ Authentication successful (Token: {token[:20]}...)\n")
        else:
            print(f"✗ Login failed: {login_resp.status_code}\n")
            headers = {}
    except Exception as e:
        print(f"✗ Auth error: {str(e)}\n")
        headers = {}
    
    # Test each endpoint
    print("[PHASE 2] ENDPOINT TESTS")
    print("-" * 80 + "\n")
    
    tests = [
        ("GET", "/authors", "Get authors list", False),
        ("GET", "/categories", "Get categories", False),
        ("GET", "/tags", "Get tags list", True),
        ("GET", "/logs", "Get activity logs", True),
        ("GET", "/articles", "Get user articles", True),
        ("GET", "/articles/public", "Get public articles", False),
        ("GET", "/latest-articles", "Get latest articles", False),
    ]
    
    passed = 0
    failed = 0
    
    for method, path, desc, protected in tests:
        url = f"{BASE_URL}{path}"
        h = headers if protected else {}
        
        try:
            if method == "GET":
                resp = requests.get(url, headers=h, timeout=5)
            elif method == "POST":
                resp = requests.post(url, headers=h, json={}, timeout=5)
            else:
                resp = requests.put(url, headers=h, json={}, timeout=5)
            
            if 200 <= resp.status_code < 300:
                print(f"✓ {resp.status_code} OK    {method:6} {path:30} {desc}")
                passed += 1
            else:
                print(f"✗ {resp.status_code} ERROR  {method:6} {path:30} {desc}")
                failed += 1
                
        except Exception as e:
            print(f"✗ ERROR  {method:6} {path:30} {desc}")
            print(f"        Exception: {str(e)}")
            failed += 1
    
    # Summary
    print("\n" + "-"*80)
    print(f"\nRESULTS: {passed} PASSED | {failed} FAILED")
    print("="*80 + "\n")
    
    return passed, failed

if __name__ == "__main__":
    passed, failed = test_endpoints()
    sys.exit(0 if failed == 0 else 1)
