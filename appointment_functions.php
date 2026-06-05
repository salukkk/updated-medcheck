<?php
// Helper functions for appointments/sessions display
// Safe to include anywhere under mecheck/ — only reads the DB and echoes HTML

function get_upcoming_sessions($database, $limit = 5) {
    $sql = "SELECT schedule.scheduleid, schedule.title, schedule.scheduledate, schedule.scheduletime, doctor.docname
            FROM schedule
            LEFT JOIN doctor ON schedule.docid = doctor.docid
            WHERE schedule.scheduledate >= CURDATE()
            ORDER BY schedule.scheduledate ASC, schedule.scheduletime ASC
            LIMIT ?";

    $stmt = $database->prepare($sql);
    if (!$stmt) return [];
    $stmt->bind_param('i', $limit);
    $stmt->execute();
    $res = $stmt->get_result();
    $out = [];
    while ($row = $res->fetch_assoc()) {
        $out[] = $row;
    }
    return $out;
}

function render_upcoming_sessions_widget($database, $limit = 5) {
    $sessions = get_upcoming_sessions($database, $limit);
    if (!$sessions) {
        echo '<li class="notification-item"><i class="fa-solid fa-info-circle"></i> No upcoming sessions found.</li>';
        return;
    }

    foreach ($sessions as $s) {
        $date = date('d M', strtotime($s['scheduledate']));
        $time = date('g:i A', strtotime($s['scheduletime']));
        $title = htmlspecialchars($s['title']);
        $doc = htmlspecialchars($s['docname'] ?? 'Doctor');
        echo "<li class=\"notification-item\"><i class=\"fa-solid fa-calendar-days\"></i> ";
        echo "<strong>$title</strong> with $doc on $date at $time.";
        echo "</li>";
    }
}

// More professional card renderer with booking action
function render_upcoming_sessions_cards($database, $userid, $limit = 5) {
    $sql = "SELECT s.scheduleid, s.title, s.scheduledate, s.scheduletime, d.docname,
                   (SELECT COUNT(*) FROM appointment a WHERE a.scheduleid = s.scheduleid AND a.pid = ?) AS booked
            FROM schedule s
            LEFT JOIN doctor d ON s.docid = d.docid
            WHERE s.scheduledate >= CURDATE()
            ORDER BY s.scheduledate ASC, s.scheduletime ASC
            LIMIT ?";

    $stmt = $database->prepare($sql);
    if (!$stmt) return;
    $stmt->bind_param('ii', $userid, $limit);
    $stmt->execute();
    $res = $stmt->get_result();

    echo '<div class="card">';
    echo '<div class="section-title"><h2><i class="fa-solid fa-calendar-days"></i> Upcoming Sessions</h2></div>';
    echo '<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:16px;margin-top:12px">';

    while ($row = $res->fetch_assoc()) {
        $sid = $row['scheduleid'];
        $title = htmlspecialchars($row['title']);
        $doc = htmlspecialchars($row['docname'] ?? 'Doctor');
        $date = date('d M, Y', strtotime($row['scheduledate']));
        $time = date('g:i A', strtotime($row['scheduletime']));
        $booked = intval($row['booked']);

        echo '<div style="background:#fff;border-radius:12px;padding:14px;border:1px solid #eef2ff;box-shadow:0 8px 18px rgba(15,23,42,0.04)">';
        echo "<h3 style=\"margin:0 0 8px 0;font-size:16px;color:#0f172a\">{$title}</h3>";
        echo "<p style=\"margin:0 0 6px 0;color:#475569;font-size:13px\"><i class=\"fa-solid fa-user-doctor\"></i> {$doc}</p>";
        echo "<p style=\"margin:0 0 12px 0;color:#64748b;font-size:13px\"><i class=\"fa-solid fa-calendar\"></i> {$date} &nbsp; <i class=\"fa-solid fa-clock\"></i> {$time}</p>";

        if ($booked > 0) {
            echo '<button class="book-btn" style="background:linear-gradient(135deg,#10b981,#059669);cursor:default">Booked</button>';
        } else {
            $link = '../patient/booking.php?id=' . urlencode($sid);
            echo "<a href=\"{$link}\"><button class=\"book-btn\" style=\"background:linear-gradient(135deg,#4facfe,#00c6ff)\">Book Now</button></a>";
        }

        echo '</div>';
    }

    echo '</div></div>';
}

?>
