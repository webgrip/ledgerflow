# Manual Tests

Manual tests are written as `.http` files compatible with:
- **VS Code**: Install the [REST Client](https://marketplace.visualstudio.com/items?itemName=humao.rest-client) extension
- **JetBrains IDEs**: Built-in HTTP Client support (`.http` files work natively)
- **curl**: Convert any request manually

## Files

| File | Coverage |
|------|----------|
| `auth.http` | Login, register, logout, auth guards |
| `accounts.http` | Accounts index/create/show, transactions, error cases |

## How to use (VS Code REST Client)

1. Open a `.http` file
2. Click **Send Request** above any `###` block
3. Set `@baseUrl` to your Sail URL (e.g. `http://localhost:8003`)
4. For authenticated requests: log in once, copy the `laravel_session` cookie value, replace `AUTHED_SESSION`

## How to get the session cookie

```bash
# Login and capture cookies
curl -c cookies.txt -X POST http://localhost:8003/login \
  -d "email=alice@demo.test&password=password" \
  -H "Content-Type: application/x-www-form-urlencoded" -L

# Use the cookie for subsequent requests
curl -b cookies.txt http://localhost:8003/accounts
```
