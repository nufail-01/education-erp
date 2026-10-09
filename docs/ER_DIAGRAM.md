# ER Diagram

```mermaid
erDiagram
    institutes ||--o{ users : has
    institutes ||--o{ teachers : has
    institutes ||--o{ roles : scopes
    institutes ||--o{ activity_logs : has
    users ||--o| teachers : profile
    users ||--o{ activity_logs : performs
    users }o--o{ roles : model_has_roles
    roles }o--o{ permissions : role_has_permissions
    users }o--o{ permissions : model_has_permissions

    institutes {
        bigint id PK
        string name
        string code UK
        string email
        string phone
        text address
        string logo
        string status
        timestamp created_at
        timestamp updated_at
    }
    users {
        bigint id PK
        bigint institute_id FK
        string name
        string email UK
        string mobile_no
        string password
        string status
        timestamp created_at
        timestamp updated_at
    }
    teachers {
        bigint id PK
        bigint institute_id FK
        bigint user_id FK
        string employee_code
        string qualification
        date joining_date
        string status
        timestamp created_at
        timestamp updated_at
    }
    roles {
        bigint id PK
        bigint institute_id FK
        string name
        string guard_name
        boolean is_protected
    }
    permissions {
        bigint id PK
        string name
        string guard_name
        boolean is_delegable
    }
    activity_logs {
        bigint id PK
        bigint institute_id FK
        bigint user_id FK
        string action
        string description
        string subject_type
        bigint subject_id
        string ip_address
        timestamp created_at
    }
```

## Constraints

- `teachers`: unique (`institute_id`, `employee_code`), unique `user_id`
- `roles`: unique (`institute_id`, `name`, `guard_name`); `institute_id` NULL means a global role
- `model_has_roles` and `model_has_permissions` carry `institute_id` as the Spatie team key (Super Admin uses 0)
- `users.institute_id` and `teachers.institute_id`: restrict on delete
