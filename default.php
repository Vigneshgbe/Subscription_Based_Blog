<?php ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Slice of Life — Coming Soon</title>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@800&family=DM+Sans:ital,wght@0,300;0,400;1,400&display=swap" rel="stylesheet">
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body {
    background: #fff;
    font-family: 'DM Sans', sans-serif;
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 2rem;
  }
  .wrap { max-width: 560px; width: 100%; }
  h1 {
    font-family: 'Montserrat', sans-serif;
    font-size: clamp(2rem, 6vw, 3.2rem);
    font-weight: 800;
    color: #111;
    letter-spacing: -0.01em;
    margin-bottom: 2rem;
  }
  .scene { margin: 0 auto 2rem; display: block; }
  .light-l { animation: blinkL 1s step-end infinite; }
  .light-r { animation: blinkR 1s step-end infinite; }
  @keyframes blinkL { 0%,100%{fill:#e53935;} 50%{fill:#555;} }
  @keyframes blinkR { 0%,100%{fill:#555;} 50%{fill:#e53935;} }
  .sign-group { transform-origin: 160px 151px; animation: sway 3s ease-in-out infinite alternate; }
  @keyframes sway { from{transform:rotate(-1deg);} to{transform:rotate(1deg);} }
  p.sub {
    font-style: italic;
    font-size: 1rem;
    color: #444;
    line-height: 1.7;
    margin-bottom: 1.6rem;
  }
  .form-row {
    display: flex;
    border: 1.5px solid #bbb;
    border-radius: 2px;
    overflow: hidden;
    max-width: 420px;
    margin: 0 auto 0.8rem;
  }
  .form-row input {
    flex: 1;
    border: none;
    outline: none;
    padding: 0.75rem 1rem;
    font-family: 'DM Sans', sans-serif;
    font-size: 0.9rem;
    color: #333;
  }
  .form-row input::placeholder { color: #aaa; }
  .form-row button {
    background: #111;
    color: #fff;
    border: none;
    padding: 0.75rem 1.2rem;
    font-family: 'Montserrat', sans-serif;
    font-size: 0.75rem;
    font-weight: 800;
    letter-spacing: 0.05em;
    cursor: pointer;
    white-space: nowrap;
    transition: background 0.2s;
  }
  .form-row button:hover { background: #333; }
  .hint { font-size: 0.78rem; color: #888; }
  .sent { display: none; font-size: 0.85rem; color: #2a7a2a; margin-top: 0.5rem; }
</style>
</head>
<body>
<div class="wrap">

  <h1>UNDER CONSTRUCTION</h1>

  <svg class="scene" width="320" height="230" viewBox="0 0 320 230" xmlns="http://www.w3.org/2000/svg">

    <!-- Ground shadow -->
    <ellipse cx="160" cy="222" rx="125" ry="7" fill="#e8e8e8"/>

    <!-- Barrier posts -->
    <rect x="74" y="146" width="10" height="72" rx="3" fill="#ccc"/>
    <rect x="236" y="146" width="10" height="72" rx="3" fill="#ccc"/>

    <!-- Barrier + sign group (sways together) -->
    <g class="sign-group">

      <!-- Barrier board stripes -->
      <clipPath id="bc"><rect x="60" y="118" width="200" height="60" rx="5"/></clipPath>
      <rect x="60" y="118" width="200" height="60" rx="5" fill="#fff"/>
      <g clip-path="url(#bc)">
        <rect x="60"  y="118" width="25" height="60" fill="#f5c800"/>
        <rect x="85"  y="118" width="25" height="60" fill="#222"/>
        <rect x="110" y="118" width="25" height="60" fill="#f5c800"/>
        <rect x="135" y="118" width="25" height="60" fill="#222"/>
        <rect x="160" y="118" width="25" height="60" fill="#f5c800"/>
        <rect x="185" y="118" width="25" height="60" fill="#222"/>
        <rect x="210" y="118" width="25" height="60" fill="#f5c800"/>
        <rect x="235" y="118" width="25" height="60" fill="#222"/>
      </g>
      <rect x="60" y="118" width="200" height="60" rx="5" fill="none" stroke="#222" stroke-width="3"/>

      <!-- Diamond sign overlay -->
      <g transform="translate(160,148) rotate(45)">
        <rect x="-32" y="-32" width="64" height="64" fill="#f5c800" stroke="#333" stroke-width="3.5"/>
      </g>
      <!-- Worker icon on diamond -->
      <g transform="translate(160,148)">
        <circle cx="0" cy="-13" r="5.5" fill="#222"/>
        <line x1="0" y1="-7" x2="0" y2="4" stroke="#222" stroke-width="3.2" stroke-linecap="round"/>
        <line x1="0" y1="4" x2="-6" y2="13" stroke="#222" stroke-width="3" stroke-linecap="round"/>
        <line x1="0" y1="4" x2="6" y2="13" stroke="#222" stroke-width="3" stroke-linecap="round"/>
        <line x1="0" y1="-1" x2="11" y2="5" stroke="#222" stroke-width="2.5" stroke-linecap="round"/>
        <line x1="11" y1="5" x2="14" y2="11" stroke="#222" stroke-width="2.5" stroke-linecap="round"/>
        <ellipse cx="14" cy="13" rx="3.5" ry="2.5" fill="#222"/>
      </g>
      <text x="160" y="172" text-anchor="middle" font-family="Arial Black,sans-serif" font-weight="900" font-size="7" fill="#222" letter-spacing="0.5">UNDER</text>
      <text x="160" y="180" text-anchor="middle" font-family="Arial Black,sans-serif" font-weight="900" font-size="6" fill="#222" letter-spacing="0.5">CONSTRUCTION</text>

      <!-- Blink lights -->
      <circle cx="74" cy="131" r="9" fill="#444" stroke="#222" stroke-width="2"/>
      <circle cx="74" cy="131" r="6" class="light-l"/>
      <circle cx="246" cy="131" r="9" fill="#444" stroke="#222" stroke-width="2"/>
      <circle cx="246" cy="131" r="6" class="light-r"/>

    </g>

    <!-- Cone 1 (tallest, leftmost) -->
    <g transform="translate(200,188)">
      <polygon points="0,-46 19,0 -19,0" fill="#e85000"/>
      <polygon points="-4,-32 4,-32 7,-21 -7,-21" fill="#fff" opacity="0.9"/>
      <polygon points="-8,-16 8,-16 11,-8 -11,-8" fill="#fff" opacity="0.9"/>
      <ellipse cx="0" cy="0" rx="19" ry="5" fill="#c84000"/>
      <rect x="-21" y="0" width="42" height="6" rx="3" fill="#bbb"/>
    </g>
    <!-- Cone 2 (mid) -->
    <g transform="translate(228,194)">
      <polygon points="0,-40 16,0 -16,0" fill="#e85000"/>
      <polygon points="-3,-27 3,-27 6,-18 -6,-18" fill="#fff" opacity="0.9"/>
      <polygon points="-7,-13 7,-13 9,-7 -9,-7" fill="#fff" opacity="0.9"/>
      <ellipse cx="0" cy="0" rx="16" ry="4.5" fill="#c84000"/>
      <rect x="-18" y="0" width="36" height="5.5" rx="2.5" fill="#bbb"/>
    </g>
    <!-- Cone 3 (smallest, rightmost) -->
    <g transform="translate(252,200)">
      <polygon points="0,-34 13,0 -13,0" fill="#e85000"/>
      <polygon points="-3,-23 3,-23 5,-15 -5,-15" fill="#fff" opacity="0.9"/>
      <polygon points="-6,-11 6,-11 8,-5 -8,-5" fill="#fff" opacity="0.9"/>
      <ellipse cx="0" cy="0" rx="13" ry="4" fill="#c84000"/>
      <rect x="-15" y="0" width="30" height="5" rx="2.5" fill="#bbb"/>
    </g>

  </svg>

  <p class="sub">
    Our website is under construction, but we are ready to<br>
    go! Special surprise for our subscribers only
  </p>

  <div class="form-row">
    <input type="email" id="email-input" placeholder="Enter a valid email address">
    <button onclick="handleNotify()">NOTIFY ME</button>
  </div>
  <p class="hint">Sign up now to get early notification of our launch date!</p>
  <p class="sent" id="sent-msg">&#10003; You're on the list! We'll notify you soon.</p>

</div>

<script>
  function handleNotify() {
    var val = document.getElementById('email-input').value.trim();
    var re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!re.test(val)) { alert('Please enter a valid email address.'); return; }
    document.querySelector('.form-row').style.display = 'none';
    document.querySelector('.hint').style.display = 'none';
    document.getElementById('sent-msg').style.display = 'block';
  }
</script>

</body>
</html>