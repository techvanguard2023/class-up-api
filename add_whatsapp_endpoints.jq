.item += [{
  "name": "💬 WhatsApp Integration",
  "item": [
    {
      "name": "Create WhatsApp Instance",
      "request": {
        "method": "POST",
        "header": [
          {"key": "Accept", "value": "application/json"},
          {"key": "Content-Type", "value": "application/json"},
          {"key": "Authorization", "value": "Bearer {{token}}"}
        ],
        "url": {
          "raw": "{{base_url}}/api/v1/whatsapp/instances",
          "host": ["{{base_url}}"],
          "path": ["api", "v1", "whatsapp", "instances"]
        },
        "body": {
          "mode": "raw",
          "raw": "{\n  \"school_id\": 1,\n  \"instance_name\": \"my-school-instance\",\n  \"webhook_url\": \"https://example.com/webhook\"\n}",
          "options": {"raw": {"language": "json"}}
        },
        "description": "Create a new WhatsApp instance"
      },
      "response": []
    },
    {
      "name": "List WhatsApp Instances",
      "request": {
        "method": "GET",
        "header": [
          {"key": "Accept", "value": "application/json"},
          {"key": "Authorization", "value": "Bearer {{token}}"}
        ],
        "url": {
          "raw": "{{base_url}}/api/v1/whatsapp/instances",
          "host": ["{{base_url}}"],
          "path": ["api", "v1", "whatsapp", "instances"]
        },
        "description": "Get all WhatsApp instances"
      },
      "response": []
    },
    {
      "name": "Get WhatsApp Instance",
      "request": {
        "method": "GET",
        "header": [
          {"key": "Accept", "value": "application/json"},
          {"key": "Authorization", "value": "Bearer {{token}}"}
        ],
        "url": {
          "raw": "{{base_url}}/api/v1/whatsapp/instances/{{instance_id}}",
          "host": ["{{base_url}}"],
          "path": ["api", "v1", "whatsapp", "instances", "{{instance_id}}"]
        },
        "description": "Get a specific WhatsApp instance"
      },
      "response": []
    },
    {
      "name": "Get QR Code / Connect Instance",
      "request": {
        "method": "GET",
        "header": [
          {"key": "Accept", "value": "application/json"},
          {"key": "Authorization", "value": "Bearer {{token}}"}
        ],
        "url": {
          "raw": "{{base_url}}/api/v1/whatsapp/instances/{{instance_id}}/connect",
          "host": ["{{base_url}}"],
          "path": ["api", "v1", "whatsapp", "instances", "{{instance_id}}", "connect"]
        },
        "description": "Get QR code and pairing code to connect WhatsApp instance"
      },
      "response": []
    },
    {
      "name": "Set Webhook",
      "request": {
        "method": "POST",
        "header": [
          {"key": "Accept", "value": "application/json"},
          {"key": "Content-Type", "value": "application/json"},
          {"key": "Authorization", "value": "Bearer {{token}}"}
        ],
        "url": {
          "raw": "{{base_url}}/api/v1/whatsapp/instances/{{instance_id}}/webhook",
          "host": ["{{base_url}}"],
          "path": ["api", "v1", "whatsapp", "instances", "{{instance_id}}", "webhook"]
        },
        "body": {
          "mode": "raw",
          "raw": "{\n  \"url\": \"https://example.com/webhook\",\n  \"enabled\": true,\n  \"webhookByEvents\": true,\n  \"webhookBase64\": true,\n  \"events\": [\"MESSAGES_UPSERT\", \"MESSAGES_UPDATE\", \"MESSAGES_DELETE\"]\n}",
          "options": {"raw": {"language": "json"}}
        },
        "description": "Set webhook configuration for WhatsApp instance"
      },
      "response": []
    },
    {
      "name": "Get Webhook Configuration",
      "request": {
        "method": "GET",
        "header": [
          {"key": "Accept", "value": "application/json"},
          {"key": "Authorization", "value": "Bearer {{token}}"}
        ],
        "url": {
          "raw": "{{base_url}}/api/v1/whatsapp/instances/{{instance_id}}/webhook",
          "host": ["{{base_url}}"],
          "path": ["api", "v1", "whatsapp", "instances", "{{instance_id}}", "webhook"]
        },
        "description": "Get webhook configuration for WhatsApp instance"
      },
      "response": []
    },
    {
      "name": "Delete WhatsApp Instance",
      "request": {
        "method": "DELETE",
        "header": [
          {"key": "Accept", "value": "application/json"},
          {"key": "Authorization", "value": "Bearer {{token}}"}
        ],
        "url": {
          "raw": "{{base_url}}/api/v1/whatsapp/instances/{{instance_id}}",
          "host": ["{{base_url}}"],
          "path": ["api", "v1", "whatsapp", "instances", "{{instance_id}}"]
        },
        "description": "Delete a WhatsApp instance"
      },
      "response": []
    }
  ]
}]
