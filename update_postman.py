import json
import os

def create_request(name, method, path, body=None, protected=True):
    header = [
        {
            "key": "Accept",
            "value": "application/json"
        }
    ]
    if protected:
        header.append({
            "key": "Authorization",
            "value": "Bearer {{token}}"
        })
    if method in ["POST", "PUT"]:
        header.append({
            "key": "Content-Type",
            "value": "application/json"
        })
    
    req = {
        "name": name,
        "request": {
            "method": method,
            "header": header,
            "url": {
                "raw": "{{base_url}}/api/v1/" + path,
                "host": ["{{base_url}}"],
                "path": ["api", "v1"] + path.split('/')
            }
        },
        "response": []
    }
    
    if body:
        req["request"]["body"] = {
            "mode": "raw",
            "raw": json.dumps(body, indent=4)
        }
    return req

def create_resource_folder(name, path, store_body=None, update_body=None):
    return {
        "name": name,
        "item": [
            create_request(f"List {name}", "GET", path),
            create_request(f"Create {name}", "POST", path, body=store_body),
            create_request(f"Show {name}", "GET", f"{path}/{{id}}"),
            create_request(f"Update {name}", "PUT", f"{path}/{{id}}", body=update_body),
            create_request(f"Delete {name}", "DELETE", f"{path}/{{id}}")
        ]
    }

collection = {
    "info": {
        "_postman_id": "class-up-api-collection",
        "name": "Class Up API",
        "description": "Collection for Class Up API v1",
        "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
    },
    "item": [
        {
            "name": "Auth",
            "item": [
                create_request("Status", "GET", "status", protected=False),
                create_request("Login", "POST", "login", body={"email": "admin@example.com", "password": "password"}, protected=False),
                create_request("Register", "POST", "register", body={
                    "name": "John",
                    "last_name": "Doe",
                    "email": "admin@example.com",
                    "phone": "123456789",
                    "password": "password",
                    "password_confirmation": "password",
                    "role": "admin",
                    "school_name": "My New School",
                    "school_type_id": 1
                }, protected=False),
                create_request("Forgot Password", "POST", "forgot-password", body={"email": "admin@example.com"}, protected=False),
                create_request("Reset Password", "POST", "reset-password", body={
                    "token": "{{reset_token}}",
                    "email": "admin@example.com",
                    "password": "newpassword",
                    "password_confirmation": "newpassword"
                }, protected=False),
                create_request("Me", "GET", "me"),
                create_request("Logout", "POST", "logout")
            ]
        },
        {
            "name": "Academic",
            "item": [
                create_resource_folder("Students", "students", store_body={"name": "Student Name", "email": "student@example.com"}),
                create_resource_folder("Classes", "classes", store_body={"name": "Math Class", "teacher_id": 1, "classroom_id": 1}),
                create_resource_folder("Subjects", "subjects", store_body={"name": "Mathematics"}),
                create_resource_folder("Classrooms", "classrooms", store_body={"name": "Room 101"}),
                create_resource_folder("Enrollments", "enrollments", store_body={"student_id": 1, "classroom_id": 1, "year": 2024}),
                create_resource_folder("Attendance", "attendances", store_body={"class_session_id": 1, "student_id": 1, "date": "2024-03-16", "status": "present"}),
                create_resource_folder("Grades", "grades", store_body={"enrollment_id": 1, "subject_id": 1, "period": "1", "value": 8.5}),
                create_resource_folder("Certificates", "certificates", store_body={"student_id": 1, "template_id": 1, "course_name": "Math 101"}),
                create_resource_folder("Certificate Templates", "certificate-templates", store_body={"name": "Default Template"})
            ]
        },
        {
            "name": "Administrative",
            "item": [
                {
                    "name": "School Types",
                    "item": [
                        create_request("List School Types", "GET", "school-types", protected=False),
                        create_request("Create School Type", "POST", "school-types", body={"name": "Public"}),
                        create_request("Show School Type", "GET", "school-types/{{id}}"),
                        create_request("Update School Type", "PUT", "school-types/{{id}}", body={"name": "Private"}),
                        create_request("Delete School Type", "DELETE", "school-types/{{id}}")
                    ]
                },
                {
                    "name": "Modalities",
                    "item": [
                        create_request("List Modalities", "GET", "modalities", protected=False),
                        create_request("Create Modality", "POST", "modalities", body={"name": "Online"}),
                        create_request("Show Modality", "GET", "modalities/{{id}}"),
                        create_request("Update Modality", "PUT", "modalities/{{id}}", body={"name": "In-person"}),
                        create_request("Delete Modality", "DELETE", "modalities/{{id}}")
                    ]
                },
                create_resource_folder("Plans", "plans", store_body={"name": "Basic Plan", "price": 29.90}),
                create_resource_folder("Guardians", "guardians", store_body={"user_id": 1, "cpf": "12345678900"})
            ]
        }
    ],
    "variable": [
        {"key": "base_url", "value": "http://localhost:8000"},
        {"key": "token", "value": "your_auth_token_here"},
        {"key": "id", "value": "1"},
        {"key": "reset_token", "value": "token_here"}
    ]
}

file_path = 'class_up_api_postman_collection.json'
with open(file_path, 'w', encoding='utf-8') as f:
    json.dump(collection, f, indent=4, ensure_ascii=False)
