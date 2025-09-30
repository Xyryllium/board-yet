#!/bin/bash

LOG_DIR="storage/logs"
AUTH_LOG="$LOG_DIR/auth.log"
ORG_LOG="$LOG_DIR/organization.log"
LARAVEL_LOG="$LOG_DIR/laravel.log"

echo "=== Laravel Log Monitoring Report ==="
echo "Generated: $(date)"
echo ""

echo "📊 Log File Sizes:"
ls -lh $LOG_DIR/*.log 2>/dev/null | awk '{print "  " $9 ": " $5}'
echo ""

echo "💾 Disk Space:"
df -h $LOG_DIR | tail -1 | awk '{print "  Available: " $4 " (" $5 " used)"}'
echo ""

echo "🚨 Recent Errors (last 10):"
if [ -f "$LARAVEL_LOG" ]; then
    grep -i "error\|exception\|fatal" $LARAVEL_LOG | tail -10 | sed 's/^/  /'
else
    echo "  No Laravel log found"
fi
echo ""

echo "🔐 Auth Events (last 24h):"
if [ -f "$AUTH_LOG" ]; then
    echo "  Verification attempts: $(grep -c "Email verification attempt" $AUTH_LOG 2>/dev/null || echo 0)"
    echo "  Successful verifications: $(grep -c "Email verification successful" $AUTH_LOG 2>/dev/null || echo 0)"
    echo "  Failed verifications: $(grep -c "Invalid verification link" $AUTH_LOG 2>/dev/null || echo 0)"
    echo "  Resend requests: $(grep -c "Resending email verification" $AUTH_LOG 2>/dev/null || echo 0)"
else
    echo "  No auth log found"
fi
echo ""

echo "🏢 Organization Events (last 24h):"
if [ -f "$ORG_LOG" ]; then
    echo "  Invitations sent: $(grep -c "Invitation email sent successfully" $ORG_LOG 2>/dev/null || echo 0)"
    echo "  Invitations accepted: $(grep -c "Invitation accepted" $ORG_LOG 2>/dev/null || echo 0)"
else
    echo "  No organization log found"
fi
echo ""

echo "⚠️  Critical Issues Check:"
CRITICAL_COUNT=$(grep -c -i "emergency\|critical\|fatal" $LOG_DIR/*.log 2>/dev/null || echo 0)
if [ "$CRITICAL_COUNT" -gt 0 ]; then
    echo "  🚨 $CRITICAL_COUNT critical issues found!"
    grep -i "emergency\|critical\|fatal" $LOG_DIR/*.log 2>/dev/null | head -5 | sed 's/^/    /'
else
    echo "  ✅ No critical issues found"
fi
echo ""

echo "🧠 Memory Usage:"
ps aux | grep "php.*artisan" | grep -v grep | awk '{print "  PHP Memory: " $6/1024 " MB"}'
echo ""

echo "=== End Report ==="
