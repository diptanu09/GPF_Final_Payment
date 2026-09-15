# ==============================================================================
# GPF Final Payment Portal - Live Docker Logs Stream
# ==============================================================================
Write-Host "Streaming logs for gpf_final_payment_app (Press Ctrl+C to stop)..." -ForegroundColor Cyan
docker compose logs -f --tail=100 app
