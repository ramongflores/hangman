<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>The Hangman Game — Online Duel</title>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Crimson+Pro:ital,wght@0,400;0,600;1,400&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
<style>
:root{
  --ink:#07070f; --paper:#f0ead8; --blood:#b92b27; --crimson:#e03c38;
  --gold:#d4a535; --gg:rgba(212,165,53,.32); --rope:#7a5c12;
  --dim:rgba(240,234,216,.07); --green:#2ecc71;
}
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{
  background:var(--ink);color:var(--paper);font-family:'Crimson Pro',serif;
  min-height:100vh;overflow-x:hidden;
  background-image:
    radial-gradient(ellipse 70% 50% at 10% 70%,rgba(185,43,39,.08) 0%,transparent 60%),
    radial-gradient(ellipse 50% 40% at 90% 15%,rgba(212,165,53,.07) 0%,transparent 55%)
}
.screen{display:none;min-height:100vh;padding:20px}
.screen.active{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:22px}
.brand{text-align:center}
.brand h1{
  font-family:'Bebas Neue',cursive;font-size:clamp(54px,12vw,108px);
  letter-spacing:5px;line-height:.88;color:var(--paper);
  text-shadow:4px 4px 0 var(--blood),8px 8px 0 rgba(185,43,39,.28)
}
.brand h1 em{color:var(--blood);font-style:normal}
.brand .sub{font-family:'Space Mono',monospace;font-size:9px;letter-spacing:4px;
  text-transform:uppercase;color:var(--gold);margin-top:10px;opacity:.7}
.status-row{display:flex;align-items:center;gap:8px;
  font-family:'Space Mono',monospace;font-size:9px;letter-spacing:2px;text-transform:uppercase;
  color:rgba(240,234,216,.38)}
.dot{width:7px;height:7px;border-radius:50%;background:rgba(240,234,216,.2);flex-shrink:0;transition:background .5s}
.dot.ok{background:var(--green);box-shadow:0 0 8px var(--green)}
.dot.err{background:var(--blood);box-shadow:0 0 6px var(--blood)}
.dot.wait{background:var(--gold);box-shadow:0 0 6px var(--gold);animation:blink-dot 1s ease-in-out infinite}
@keyframes blink-dot{0%,100%{opacity:1}50%{opacity:.3}}
.gallows-deco{animation:sway 4s ease-in-out infinite;transform-origin:50% 0}
@keyframes sway{0%,100%{transform:rotate(-1deg)}50%{transform:rotate(1deg)}}
.form-col{display:flex;flex-direction:column;gap:12px;width:100%;max-width:370px}
label.lbl{display:block;font-family:'Space Mono',monospace;font-size:9px;
  letter-spacing:3px;text-transform:uppercase;color:var(--gold);margin-bottom:6px}
input[type=text]{
  width:100%;background:var(--dim);border:1px solid rgba(212,165,53,.22);
  color:var(--paper);font-family:'Space Mono',monospace;font-size:14px;
  padding:13px 16px;outline:none;letter-spacing:2px;text-align:center;
  transition:border-color .2s,box-shadow .2s;border-radius:0;-webkit-appearance:none
}
input[type=text]:focus{border-color:var(--gold);box-shadow:0 0 22px var(--gg)}
input[type=text]::placeholder{opacity:.25;font-style:italic;letter-spacing:1px}
.divider{display:flex;align-items:center;gap:10px;
  font-family:'Space Mono',monospace;font-size:9px;letter-spacing:3px;color:rgba(240,234,216,.18)}
.divider::before,.divider::after{content:'';flex:1;height:1px;background:rgba(240,234,216,.07)}
.join-row{display:flex;gap:8px}.join-row input{flex:1}
.btn{font-family:'Bebas Neue',cursive;letter-spacing:3px;font-size:19px;
  padding:13px 28px;border:none;cursor:pointer;transition:all .14s ease;white-space:nowrap}
.btn:disabled{opacity:.4;cursor:not-allowed}
.btn-p{background:var(--blood);color:var(--paper);
  clip-path:polygon(8px 0%,100% 0%,calc(100% - 8px) 100%,0% 100%);
  box-shadow:3px 3px 0 rgba(0,0,0,.5)}
.btn-p:hover:not(:disabled){background:var(--crimson);transform:translate(-2px,-2px);box-shadow:5px 5px 0 rgba(0,0,0,.5)}
.btn-s{background:transparent;color:var(--gold);border:1px solid rgba(212,165,53,.4);
  clip-path:polygon(8px 0%,100% 0%,calc(100% - 8px) 100%,0% 100%)}
.btn-s:hover:not(:disabled){background:rgba(212,165,53,.09)}
.btn-g{background:transparent;color:rgba(240,234,216,.35);
  border:1px solid rgba(240,234,216,.1);font-size:13px;padding:8px 18px;letter-spacing:2px}
.btn-g:hover:not(:disabled){color:var(--paper);border-color:rgba(240,234,216,.3)}
#waiting{gap:24px;text-align:center}
.code-card{position:relative;padding:22px 48px;border:1px solid rgba(212,165,53,.38);
  background:rgba(212,165,53,.04);cursor:pointer;user-select:none}
.code-card::before{content:'ROOM CODE';position:absolute;top:-9px;left:50%;
  transform:translateX(-50%);background:var(--ink);padding:0 10px;
  font-family:'Space Mono',monospace;font-size:8px;letter-spacing:3px;color:var(--gold)}
.code-big{font-family:'Bebas Neue',cursive;font-size:58px;letter-spacing:13px;
  color:var(--gold);text-shadow:0 0 30px var(--gg)}
.copy-hint{font-family:'Space Mono',monospace;font-size:8px;letter-spacing:2px;
  color:rgba(240,234,216,.22);margin-top:4px}
.players-row{display:flex;gap:18px;align-items:center;justify-content:center;flex-wrap:wrap}
.p-slot{width:130px;height:94px;border:1px dashed rgba(240,234,216,.14);
  display:flex;flex-direction:column;align-items:center;justify-content:center;gap:5px;transition:all .35s}
.p-slot.filled{border-style:solid;border-color:rgba(212,165,53,.4);background:rgba(212,165,53,.05)}
.p-slot.me{border-color:rgba(46,204,113,.45);background:rgba(46,204,113,.05)}
.p-av{font-size:26px;line-height:1}.p-nm{font-family:'Space Mono',monospace;font-size:11px;letter-spacing:1px;color:var(--gold)}
.p-tg{font-family:'Space Mono',monospace;font-size:8px;letter-spacing:2px;text-transform:uppercase;color:rgba(240,234,216,.28)}
.p-slot.filled .p-tg{color:rgba(212,165,53,.6)}.p-slot.me .p-tg{color:rgba(46,204,113,.7)}
.vs-txt{font-family:'Bebas Neue',cursive;font-size:26px;color:var(--blood);padding:0 4px}
#wait-msg{font-style:italic;font-size:14px;color:rgba(240,234,216,.35);text-align:center;max-width:340px}
.spin{display:inline-block;animation:spin 1s linear infinite}
@keyframes spin{to{transform:rotate(360deg)}}
.dots::after{content:'';animation:dd 1.5s steps(4,end) infinite}
@keyframes dd{0%{content:''}25%{content:'.'}50%{content:'..'}75%{content:'...'}100%{content:''}}
#setup{gap:22px;text-align:center;max-width:460px;width:100%}
.setup-h{font-family:'Bebas Neue',cursive;font-size:38px;letter-spacing:4px}
.setup-h span{color:var(--gold)}
.setup-sub{font-size:14px;font-style:italic;color:rgba(240,234,216,.4)}
.char-c{font-family:'Space Mono',monospace;font-size:9px;letter-spacing:2px;
  color:rgba(240,234,216,.22);text-align:right;margin-top:4px}
.timer-opt{display:flex;gap:8px;flex-wrap:wrap;justify-content:center;margin-top:4px}
.timer-btn{font-family:'Space Mono',monospace;font-size:10px;letter-spacing:2px;
  padding:7px 14px;background:var(--dim);border:1px solid rgba(240,234,216,.12);
  color:rgba(240,234,216,.5);cursor:pointer;transition:all .15s;text-transform:uppercase}
.timer-btn:hover{border-color:rgba(212,165,53,.4);color:var(--gold)}
.timer-btn.sel{border-color:var(--gold);color:var(--gold);background:rgba(212,165,53,.08)}
.hdr-right{display:flex;align-items:center;gap:10px}
.timer-wrap{display:flex;flex-direction:column;align-items:center;gap:3px;min-width:54px}
.timer-ring{position:relative;width:48px;height:48px}
.timer-ring svg{width:48px;height:48px;transform:rotate(-90deg)}
.timer-track{fill:none;stroke:rgba(240,234,216,.1);stroke-width:4}
.timer-arc{fill:none;stroke:var(--gold);stroke-width:4;stroke-linecap:round;
  stroke-dasharray:120;stroke-dashoffset:0;transition:stroke-dashoffset .95s linear,stroke .3s}
.timer-arc.urgent{stroke:var(--blood);animation:tpulse .5s ease-in-out infinite}
@keyframes tpulse{0%,100%{stroke-opacity:1}50%{stroke-opacity:.35}}
.timer-num{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;
  font-family:'Bebas Neue',cursive;font-size:16px;color:var(--paper);transition:color .3s}
.timer-num.urgent{color:var(--blood)}
.timer-lbl{font-family:'Space Mono',monospace;font-size:7px;letter-spacing:2px;
  text-transform:uppercase;color:rgba(240,234,216,.28)}
.timer-wrap.off{opacity:.18;pointer-events:none}
#game{display:none;min-height:100vh;flex-direction:column;width:100%;
  max-width:960px;margin:0 auto;padding:0;align-items:stretch}
#game.active{display:flex}
.g-hdr{display:flex;justify-content:space-between;align-items:center;
  padding:14px 22px;border-bottom:1px solid rgba(240,234,216,.07);flex-wrap:wrap;gap:10px}
.sboard{display:flex;gap:6px;align-items:stretch}
.sc{padding:7px 16px;text-align:center;min-width:84px;
  border:1px solid rgba(240,234,216,.08);transition:border-color .3s}
.sc.gsr{border-color:var(--gold);background:rgba(212,165,53,.06)}
.sc.str{border-color:rgba(185,43,39,.35)}
.sc-nm{font-family:'Space Mono',monospace;font-size:8px;letter-spacing:2px;text-transform:uppercase;color:rgba(240,234,216,.38)}
.sc-pt{font-family:'Bebas Neue',cursive;font-size:32px;color:var(--gold);line-height:1.05}
.sc-rl{font-family:'Space Mono',monospace;font-size:7px;letter-spacing:1px;color:rgba(240,234,216,.18)}
.sc-vs{font-family:'Bebas Neue',cursive;font-size:20px;color:var(--blood);align-self:center;padding:0 3px}
.rnd-lbl{font-family:'Space Mono',monospace;font-size:9px;letter-spacing:3px;color:rgba(240,234,216,.28);text-transform:uppercase}
.tbadge{padding:7px 14px;font-family:'Space Mono',monospace;font-size:9px;
  letter-spacing:2px;text-transform:uppercase;border:1px solid rgba(240,234,216,.1);color:rgba(240,234,216,.32)}
.tbadge.mt{border-color:var(--green);color:var(--green);background:rgba(46,204,113,.07);animation:bl 1.8s ease-in-out infinite}
.tbadge.wb{border-color:var(--gold);color:var(--gold);background:rgba(212,165,53,.07)}
.tbadge.lb{border-color:var(--blood);color:var(--blood);background:rgba(185,43,39,.07)}
@keyframes bl{0%,100%{opacity:1}50%{opacity:.42}}
.arena{flex:1;display:grid;grid-template-columns:1fr 1fr}
@media(max-width:660px){.arena{grid-template-columns:1fr}.gp{border-right:none;border-bottom:1px solid rgba(240,234,216,.07);min-height:220px}}
.gp{border-right:1px solid rgba(240,234,216,.07);display:flex;align-items:center;
  justify-content:center;padding:20px;position:relative;overflow:hidden}
.gp::before{content:'';position:absolute;inset:0;
  background:radial-gradient(circle at 55% 42%,rgba(185,43,39,.06) 0%,transparent 65%);pointer-events:none}
.g-svg{width:100%;max-width:280px;height:auto}
.hp{opacity:0;transition:opacity .5s ease}.hp.on{opacity:1}
.bp{stroke:var(--paper);stroke-width:3;stroke-linecap:round;fill:none}
.hd{stroke:var(--paper);stroke-width:3;fill:none}
.fd{stroke:var(--blood);stroke-width:2;stroke-linecap:round;fill:none;opacity:0}.fd.on{opacity:1}
.qp{display:flex;flex-direction:column;padding:18px 16px;gap:16px;overflow-y:auto}
.wr{display:flex;flex-wrap:wrap;gap:8px 14px;justify-content:center;padding:4px;align-items:flex-end}
.word-group{display:flex;gap:5px;flex-shrink:0;align-items:flex-end}
.lb{width:32px;height:42px;border-bottom:2px solid rgba(240,234,216,.28);
  display:flex;align-items:center;justify-content:center;
  font-family:'Bebas Neue',cursive;font-size:24px;color:var(--paper)}
.lb.sp{border-bottom-color:transparent;width:16px}
.lb.rv{border-bottom-color:var(--gold)}
.lb.pp{animation:lp .42s ease forwards}
@keyframes lp{0%{transform:scale(0) rotateY(90deg);color:var(--gold)}60%{transform:scale(1.28) rotateY(0);color:var(--gold)}100%{transform:scale(1);color:var(--paper)}}
.hbox{background:rgba(212,165,53,.05);border:1px solid rgba(212,165,53,.18);padding:9px 14px;text-align:center}
.hlbl{font-family:'Space Mono',monospace;font-size:8px;letter-spacing:3px;text-transform:uppercase;color:var(--gold);display:block;margin-bottom:3px}
.htxt{font-style:italic;font-size:14px;color:rgba(240,234,216,.62)}
.wsec{display:flex;flex-direction:column;gap:6px}
.wlbl{font-family:'Space Mono',monospace;font-size:8px;letter-spacing:3px;text-transform:uppercase;color:var(--blood)}
.wrow{display:flex;flex-wrap:wrap;gap:4px;min-height:24px}
.wl{font-family:'Bebas Neue',cursive;font-size:18px;color:var(--blood);padding:0 5px;
  border:1px solid rgba(185,43,39,.3);animation:wp .28s ease}
@keyframes wp{0%{transform:scale(2.5);opacity:0}100%{transform:scale(1);opacity:1}}
.kbd{display:flex;flex-direction:column;gap:4px;align-items:center}
.kb-row{display:flex;gap:4px;flex-wrap:wrap;justify-content:center}
.k{width:32px;height:32px;background:rgba(240,234,216,.05);border:1px solid rgba(240,234,216,.1);
  color:var(--paper);font-family:'Bebas Neue',cursive;font-size:16px;cursor:pointer;
  transition:all .1s;display:flex;align-items:center;justify-content:center;border-radius:0}
.k:hover:not(:disabled){border-color:var(--gold);background:rgba(212,165,53,.12);transform:translateY(-1px)}
.k:active:not(:disabled){transform:translateY(1px)}
.k.kr{background:rgba(46,204,113,.1);border-color:rgba(46,204,113,.3);color:rgba(240,234,216,.22);cursor:default}
.k.kw{background:rgba(185,43,39,.1);border-color:rgba(185,43,39,.28);color:rgba(240,234,216,.2);cursor:default}
.k:disabled:not(.kr):not(.kw){opacity:.3;cursor:not-allowed}
#result{gap:26px;text-align:center}
.rt{font-family:'Bebas Neue',cursive;font-size:clamp(50px,12vw,100px);
  letter-spacing:5px;line-height:1;animation:rb .55s cubic-bezier(.17,.67,.28,1.35) both}
@keyframes rb{0%{transform:scale(.3);opacity:0}70%{transform:scale(1.1)}100%{transform:scale(1);opacity:1}}
.rw{color:var(--gold);text-shadow:0 0 40px var(--gg)}
.rl{color:var(--blood);text-shadow:0 0 40px rgba(185,43,39,.45)}
.rd{color:var(--paper)}
.res-w{font-family:'Space Mono',monospace;font-size:10px;letter-spacing:2px;color:rgba(240,234,216,.32);text-transform:uppercase}
.res-wb{font-family:'Bebas Neue',cursive;font-size:clamp(22px,5vw,42px);letter-spacing:7px;color:var(--gold);display:block;margin-top:6px}
.fin-row{display:flex;gap:14px;justify-content:center;align-items:stretch;flex-wrap:wrap}
.fcard{padding:18px 24px;border:1px solid rgba(240,234,216,.08);min-width:106px;text-align:center}
.fcard.win{border-color:var(--gold);background:rgba(212,165,53,.07);position:relative}
.fcard.win::before{content:'👑';position:absolute;top:-15px;left:50%;transform:translateX(-50%);font-size:20px}
.fav{font-size:22px;margin-bottom:4px}
.fn{font-family:'Space Mono',monospace;font-size:8px;letter-spacing:2px;text-transform:uppercase;color:rgba(240,234,216,.36)}
.fps{font-family:'Bebas Neue',cursive;font-size:50px;color:var(--gold);line-height:1}
.fin-vs{font-family:'Bebas Neue',cursive;font-size:20px;color:var(--blood);align-self:center}
.btn-row{display:flex;gap:12px;justify-content:center;flex-wrap:wrap}
#toast{position:fixed;top:18px;left:50%;transform:translateX(-50%) translateY(-90px);
  background:#0c0c16;border:1px solid rgba(212,165,53,.4);color:var(--paper);
  font-family:'Space Mono',monospace;font-size:10px;letter-spacing:2px;text-transform:uppercase;
  padding:11px 22px;z-index:9999;transition:transform .32s cubic-bezier(.34,1.56,.64,1);
  white-space:nowrap;max-width:92vw;text-align:center}
#toast.show{transform:translateX(-50%) translateY(0)}
#toast.err{border-color:rgba(185,43,39,.6);color:var(--crimson)}
#toast.ok{border-color:rgba(46,204,113,.6);color:var(--green)}
@media(max-width:460px){
  .k{width:27px;height:27px;font-size:13px}
  .lb{width:27px;height:36px;font-size:20px}
  .code-big{font-size:40px;letter-spacing:8px}
  .g-hdr{padding:10px 12px}
}
</style>
</head>
<body>
<div id="toast"></div>

<!-- ══ LOBBY ══ -->
<div class="screen active" id="lobby">
  <div class="brand">
    <h1>The<br><em>Hangman</em> Game</h1>
    <p class="sub">Online duel</p>
  </div>
  <div class="status-row">
    <div class="dot ok" id="cdot"></div>
    <span id="ctxt">Ready to play</span>
  </div>
  <svg class="gallows-deco" width="72" height="80" viewBox="0 0 72 80" fill="none">
    <line x1="4" y1="78" x2="68" y2="78" stroke="#7a5c12" stroke-width="3" stroke-linecap="round"/>
    <line x1="16" y1="78" x2="16" y2="4" stroke="#7a5c12" stroke-width="4" stroke-linecap="round"/>
    <line x1="16" y1="4" x2="44" y2="4" stroke="#7a5c12" stroke-width="3" stroke-linecap="round"/>
    <line x1="44" y1="4" x2="44" y2="14" stroke="#7a5c12" stroke-width="2.5"/>
    <circle cx="44" cy="22" r="7" stroke="#f0ead8" stroke-width="2.5"/>
    <line x1="44" y1="29" x2="44" y2="48" stroke="#f0ead8" stroke-width="2.5" stroke-linecap="round"/>
    <line x1="44" y1="35" x2="36" y2="45" stroke="#f0ead8" stroke-width="2.5" stroke-linecap="round"/>
    <line x1="44" y1="35" x2="52" y2="45" stroke="#f0ead8" stroke-width="2.5" stroke-linecap="round"/>
    <line x1="44" y1="48" x2="37" y2="61" stroke="#f0ead8" stroke-width="2.5" stroke-linecap="round"/>
    <line x1="44" y1="48" x2="51" y2="61" stroke="#f0ead8" stroke-width="2.5" stroke-linecap="round"/>
  </svg>
  <div class="form-col">
    <div>
      <label class="lbl">Your player name</label>
      <input type="text" id="inp-name" placeholder="Enter your name" maxlength="24" autocomplete="off"/>
    </div>
    <button class="btn btn-p" id="btn-create" onclick="createRoom()">⚔️ Create new room</button>
    <div class="divider">or join with code</div>
    <div class="join-row">
      <input type="text" id="inp-code" placeholder="XXXXXX" maxlength="6" autocomplete="off"/>
      <button class="btn btn-s" id="btn-join" onclick="joinRoom()">Join</button>
    </div>
  </div>
  <div style="margin-top: 1.5rem; padding-top: 1.5rem; border-top: 1px solid rgba(255,255,255,0.2); text-align: center; font-size: 1.2rem; opacity: 0.8;">
                <p>Developed by - <a href="https://monchdev.com" style="color: inherit; text-decoration: none;">MonchDev</a></p>
            </div>
</div>

<!-- ══ WAITING ══ -->
<div class="screen" id="waiting">
  <div class="code-card" onclick="copyCode()">
    <div class="code-big" id="disp-code">------</div>
    <div class="copy-hint">📋 Tap to copy · Share this code with your opponent</div>
  </div>
  <div class="players-row">
    <div class="p-slot" id="slot-0"><div class="p-av">👤</div><div class="p-nm">Waiting...</div><div class="p-tg">empty</div></div>
    <div class="vs-txt">VS</div>
    <div class="p-slot" id="slot-1"><div class="p-av">👤</div><div class="p-nm">Waiting...</div><div class="p-tg">empty</div></div>
  </div>
  <p id="wait-msg"><span class="spin">⟳</span> Waiting for your opponent to enter the code<span class="dots"></span></p>
  <button class="btn btn-g" onclick="leaveRoom()">← Leave</button>
</div>

<!-- ══ SETUP ══ -->
<div class="screen" id="setup">
  <div class="setup-h">Choose your <span>word</span></div>
  <p class="setup-sub" id="setup-sub">Your opponent must guess it</p>
  <div style="width:100%;max-width:440px;display:flex;flex-direction:column;gap:12px">
    <div>
      <label class="lbl">Secret word or phrase</label>
      <input type="text" id="inp-word" placeholder="TYPE HERE" maxlength="36" autocomplete="off"/>
      <div class="char-c" id="char-c">0 / 24</div>
    </div>
    <div>
      <label class="lbl">Hint or category (optional)</label>
      <input type="text" id="inp-hint" placeholder="Ex: It's an animal, A famous city..." maxlength="60" autocomplete="off"/>
    </div>
    <div>
      <label class="lbl">⏱ Timer per turn</label>
      <div class="timer-opt">
        <button class="timer-btn sel" onclick="selTimer(this,0)">No limit</button>
        <button class="timer-btn" onclick="selTimer(this,30)">30 sec</button>
        <button class="timer-btn" onclick="selTimer(this,60)">60 seg</button>
        <button class="timer-btn" onclick="selTimer(this,90)">90 seg</button>
      </div>
    </div>
    <button class="btn btn-p" style="width:100%;margin-top:6px" id="btn-send" onclick="submitWord()">
      Confirm and send 🔒
    </button>
  </div>
</div>

<!-- ══ GAME ══ -->
<div class="screen" id="game">
  <div class="g-hdr">
    <div class="sboard">
      <div class="sc" id="sc0"><div class="sc-nm" id="sc0n">J1</div><div class="sc-pt" id="sc0p">0</div><div class="sc-rl" id="sc0r"></div></div>
      <div class="sc-vs">VS</div>
      <div class="sc" id="sc1"><div class="sc-nm" id="sc1n">J2</div><div class="sc-pt" id="sc1p">0</div><div class="sc-rl" id="sc1r"></div></div>
    </div>
    <div class="rnd-lbl" id="rnd-lbl">ROUND 1</div>
    <div class="hdr-right">
      <div class="timer-wrap off" id="timer-wrap">
        <div class="timer-ring">
          <svg viewBox="0 0 48 48">
            <circle class="timer-track" cx="24" cy="24" r="19"/>
            <circle class="timer-arc" id="timer-arc" cx="24" cy="24" r="19"/>
          </svg>
          <div class="timer-num" id="timer-num">—</div>
        </div>
        <div class="timer-lbl">seg</div>
      </div>
      <div class="tbadge" id="turn-b">...</div>
    </div>
  </div>
  <div class="arena">
    <div class="gp">
      <svg class="g-svg" viewBox="0 0 260 295">
        <line x1="10" y1="290" x2="250" y2="290" stroke="#7a5c12" stroke-width="5" stroke-linecap="round"/>
        <line x1="50" y1="290" x2="50" y2="12" stroke="#7a5c12" stroke-width="5" stroke-linecap="round"/>
        <line x1="50" y1="12" x2="162" y2="12" stroke="#7a5c12" stroke-width="4" stroke-linecap="round"/>
        <line x1="50" y1="44" x2="82" y2="12" stroke="#7a5c12" stroke-width="3"/>
        <line x1="162" y1="12" x2="162" y2="42" stroke="#7a5c12" stroke-width="3"/>
        <line x1="162" y1="42" x2="162" y2="54" stroke="#7a5c12" stroke-width="2.5" class="hp" id="p0"/>
        <circle cx="162" cy="73" r="19" class="hp hd" id="p1"/>
        <line x1="155" y1="67" x2="160" y2="72" class="hp fd" id="fd1"/>
        <line x1="160" y1="67" x2="155" y2="72" class="hp fd" id="fd2"/>
        <line x1="165" y1="67" x2="170" y2="72" class="hp fd" id="fd3"/>
        <line x1="170" y1="67" x2="165" y2="72" class="hp fd" id="fd4"/>
        <path d="M154 81 Q162 75 170 81" class="hp fd" id="fd5" style="stroke-width:1.5"/>
        <line x1="162" y1="92" x2="162" y2="166" class="hp bp" id="p2"/>
        <line x1="162" y1="110" x2="132" y2="146" class="hp bp" id="p3"/>
        <line x1="162" y1="110" x2="192" y2="146" class="hp bp" id="p4"/>
        <line x1="162" y1="166" x2="132" y2="218" class="hp bp" id="p5"/>
        <line x1="162" y1="166" x2="192" y2="218" class="hp bp" id="p6"/>
      </svg>
    </div>
    <div class="qp">
      <div class="wr" id="word-row"></div>
      <div class="hbox" id="hbox" style="display:none">
        <span class="hlbl">💡 Hint</span><span class="htxt" id="htxt"></span>
      </div>
      <div class="wsec">
        <div class="wlbl">Wrong — <span id="wcount">0</span>/6</div>
        <div class="wrow" id="wrow"></div>
      </div>
      <div class="kbd" id="kbd"></div>
    </div>
  </div>
</div>

<!-- ══ RESULT ══ -->
<div class="screen" id="result">
  <div class="rt rd" id="res-title">Fin</div>
  <div class="res-w">The word was:<span class="res-wb" id="res-word">—</span></div>
  <div class="fin-row" id="fin-row"></div>
  <div class="btn-row">
    <button class="btn btn-p" onclick="nextRound()">Next round ⚔️</button>
    <button class="btn btn-g" onclick="leaveRoom()">← Salir</button>
  </div>
</div>

<script>
// ═══════════════════════════════════════════════════════════════
//  ARCHITECTURE: PHP Long-polling
//  Each player POSTs to signal.php to send messages and
//  GETs ?a=poll to receive new messages. No WebRTC.
//  Works on any shared PHP hosting.
// ═══════════════════════════════════════════════════════════════

// ── CONFIG — adjust this path if needed ──
const SIGNAL_URL = 'signal.php';

const AVATARS  = ['🤠','🧙','🦸','🥷','👸','🤖','👻','🎭','🦊','🐯'];
const PARTS    = ['p0','p1','p2','p3','p4','p5','p6'];
const FACE     = ['fd1','fd2','fd3','fd4','fd5'];
const ROWS     = [
  ['1','2','3','4','5','6','7','8','9','0'],
  ['Q','W','E','R','T','Y','U','I','O','P'],
  ['A','S','D','F','G','H','J','K','L','Ñ'],
  ['Z','X','C','V','B','N','M','Ü']
];
const MAX_WRONG = 6;

// ── ESTADO DEL JUEGO ──
let G = {
  myIdx: 0,
  players: [{name:'',score:0,av:''},{name:'',score:0,av:''}],
  round: 1,
  word: '', hint: '',
  guessed: [], wrong: [],
  setter: 0, guesser: 1,
  phase: 'lobby',
  result: null,
  timerSecs: 0
};

let roomCode     = null;
let pollActive   = false;
let lastMsgTs    = 0;
let timerInterval = null;
let timerLeft    = 0;
let chosenTimer  = 0;

// ══════════════════════════════
//  SIGNAL: enviar y recibir
// ══════════════════════════════
async function sendMsg(msg) {
  if(!roomCode) return;
  try {
    await fetch(SIGNAL_URL + '?r=' + roomCode, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ from: G.myIdx, msg })
    });
  } catch(e) {
    console.warn('sendMsg error:', e);
  }
}

let pollTimer = null;

async function doPoll() {
  if(!pollActive || !roomCode) return;
  try {
    const res = await fetch(
      SIGNAL_URL + '?a=poll&r=' + roomCode + '&since=' + lastMsgTs,
      { signal: AbortSignal.timeout(4000) }
    );
    if(res.ok) {
      const data = await res.json();
      if(data.msgs && data.msgs.length > 0) {
        for(const m of data.msgs) {
          if(m.ts > lastMsgTs) lastMsgTs = m.ts;
          if(m.from !== G.myIdx) handleMsg(m.msg);
        }
      }
    }
  } catch(e) {
    if(e.name !== 'AbortError') console.warn('poll error:', e.message);
  }
}

function startPolling() {
  pollActive = true;
  doPoll();
  pollTimer = setInterval(doPoll, 500);
}

function stopPolling() {
  pollActive = false;
  if(pollTimer) { clearInterval(pollTimer); pollTimer = null; }
}

// ══════════════════════════════
//  CREAR SALA
// ══════════════════════════════
window.createRoom = async function() {
  const name = document.getElementById('inp-name').value.trim();
  if(!name) { toast('Enter your name first','err'); return; }

  const btn = document.getElementById('btn-create');
  btn.disabled = true; btn.textContent = 'Creando...';
  setConn('wait','Creating room...');

  const code = genCode();
  try {
    const res = await fetch(SIGNAL_URL + '?a=create&r=' + code);
    const data = await res.json();
    if(!data.ok) throw new Error('create failed');
  } catch(e) {
    toast('Error creating room. Is signal.php on the server?','err');
    setConn('err','Server error');
    btn.disabled = false; btn.textContent = '⚔️ Crear sala nueva';
    return;
  }

  roomCode = code;
  G.myIdx = 0;
  G.players[0] = { name, score:0, av: AVATARS[Math.floor(Math.random()*5)] };
  G.players[1] = { name:'', score:0, av:'' };
  G.round = 1; G.phase = 'waiting'; G.setter = 0; G.guesser = 1;
  G.word=''; G.hint=''; G.guessed=[]; G.wrong=[]; G.result=null;
  localStorage.setItem('ahorcado_name', name);

  btn.disabled = false; btn.textContent = '⚔️ Crear sala nueva';
  setConn('ok','Connected');
  document.getElementById('disp-code').textContent = roomCode;
  updateSlots();
  showScreen('waiting');
  startPolling();
  toast('Room ' + roomCode + ' created ✓','ok');
};

// ══════════════════════════════
//  UNIRSE A SALA
// ══════════════════════════════
window.joinRoom = async function() {
  const name = document.getElementById('inp-name').value.trim();
  const code = document.getElementById('inp-code').value.trim().toUpperCase();
  if(!name) { toast('Escribe tu nombre primero','err'); return; }
  if(code.length < 4) { toast('Invalid code','err'); return; }

  const btn = document.getElementById('btn-join');
  btn.disabled = true; btn.textContent = 'Connecting...';
  setConn('wait','Looking for room...');
  localStorage.setItem('ahorcado_name', name);

  try {
    const res = await fetch(SIGNAL_URL + '?a=exists&r=' + code);
    const data = await res.json();
    if(!data.exists) {
      toast('Room not found — check the code','err');
      setConn('ok','Listo para jugar');
      btn.disabled = false; btn.textContent = 'Join';
      return;
    }
  } catch(e) {
    toast('Could not connect to the server','err');
    setConn('err','Error de servidor');
    btn.disabled = false; btn.textContent = 'Unirse';
    return;
  }

  roomCode = code;
  G.myIdx = 1;
  G.players[1] = { name, score:0, av: AVATARS[5 + Math.floor(Math.random()*5)] };
  btn.disabled = false; btn.textContent = 'Unirse';
  setConn('ok','Connected');
  document.getElementById('disp-code').textContent = roomCode;
  showScreen('waiting');
  startPolling();

  // Notificar al host
  sendMsg({ type:'GUEST_JOIN', payload:{ guestName: name, guestAv: G.players[1].av }});
  toast('Connected!','ok');
};

// ══════════════════════════════
//  MANEJADOR DE MENSAJES
// ══════════════════════════════
function handleMsg(msg) {
  const { type, payload } = msg;
  switch(type) {

    case 'GUEST_JOIN':
      G.players[1] = { name: payload.guestName, score:0, av: payload.guestAv };
      updateSlots();
      document.getElementById('wait-msg').innerHTML =
        '✅ ' + payload.guestName + ' joined! Starting<span class="dots"></span>';
      setTimeout(() => {
        // Enviar estado al guest
        sendMsg({ type:'HELLO', payload:{
          hostName: G.players[0].name, hostAv: G.players[0].av,
          round: G.round, setter: G.setter,
          score0: G.players[0].score, score1: G.players[1].score
        }});
        if(G.setter === 0) startSetup();
        else { sendMsg({ type:'GO_SETUP', payload:{} }); showWaitingForWord(); }
      }, 600);
      break;

    case 'HELLO':
      G.players[0] = { name: payload.hostName, score: payload.score0||0, av: payload.hostAv };
      G.players[1].score = payload.score1 || 0;
      G.round = payload.round || 1;
      G.setter = payload.setter || 0;
      G.guesser = 1 - G.setter;
      updateSlots();
      document.getElementById('wait-msg').innerHTML =
        '✅ Connected with ' + payload.hostName + '! Starting<span class="dots"></span>';
      break;

    case 'GO_SETUP':
      startSetup();
      break;

    case 'WORD_SET':
      G.word = payload.word; G.hint = payload.hint || '';
      G.guessed = []; G.wrong = []; G.phase = 'playing';
      G.timerSecs = payload.timerSecs || 0;
      renderGame(); showScreen('game');
      startTimer();
      break;

    case 'GUESS':
      applyGuess(payload.letter);
      break;

    case 'STATE_UPDATE':
      G.guessed = payload.guessed; G.wrong = payload.wrong;
      G.phase = payload.phase || 'playing';
      G.players[0].score = payload.score0;
      G.players[1].score = payload.score1;
      if(payload.result) G.result = payload.result;
      renderGame();
      if(G.phase === 'result' && G.result) {
        stopTimer();
        setTimeout(() => showResult(G.result), 500);
      } else if(G.phase === 'playing') {
        startTimer();
      }
      break;

    case 'NEXT_ROUND':
      stopTimer();
      G.round = payload.round; G.setter = payload.setter; G.guesser = 1 - G.setter;
      G.word=''; G.hint=''; G.guessed=[]; G.wrong=[]; G.result=null;
      G.players[0].score = payload.score0; G.players[1].score = payload.score1;
      if(G.myIdx === G.setter) startSetup();
      else showWaitingForWord();
      break;
  }
}

// ══════════════════════════════
//  FLUJO DEL JUEGO
// ══════════════════════════════
function startSetup() {
  G.phase = 'setup';
  chosenTimer = 0;
  document.querySelectorAll('.timer-btn').forEach((b,i) => b.classList.toggle('sel', i===0));
  const guesserName = G.players[1 - G.setter]?.name || 'Rival';
  document.getElementById('setup-sub').textContent = guesserName + ' must guess it';
  document.getElementById('inp-word').value = '';
  document.getElementById('inp-hint').value = '';
  document.getElementById('char-c').textContent = '0 / 24';
  const sb = document.getElementById('btn-send');
  if(sb) { sb.disabled=false; sb.textContent='Confirm and send 🔒'; }
  showScreen('setup');
}

function showWaitingForWord() {
  showScreen('waiting');
  const setterName = G.players[G.setter]?.name || 'Rival';
  document.getElementById('wait-msg').innerHTML =
    '<span class="spin">⟳</span> ' + setterName + ' is choosing the word<span class="dots"></span>';
}

window.submitWord = function() {
  const word = document.getElementById('inp-word').value.trim().toUpperCase();
  const hint = document.getElementById('inp-hint').value.trim();
  if(word.replace(/ /g,'').length < 2) { toast('Write at least 2 characters','err'); return; }
  if(!/[A-ZÁÉÍÓÚÜÑ0-9]/.test(word)) { toast('Use valid letters or numbers','err'); return; }

  const sb = document.getElementById('btn-send');
  if(sb) { sb.disabled=true; sb.textContent='Sending...'; }

  G.word = word; G.hint = hint;
  G.guessed = []; G.wrong = []; G.phase = 'playing';
  G.timerSecs = chosenTimer;

  sendMsg({ type:'WORD_SET', payload:{ word, hint, timerSecs: chosenTimer }});
  renderGame(); showScreen('game');
  startTimer();
};

window.guessLetter = function(letter) {
  if(G.phase !== 'playing') return;
  if(G.myIdx !== G.guesser) return;
  if(G.guessed.includes(letter) || G.wrong.includes(letter)) return;
  if(isWin() || isLose()) return;
  const kb = document.getElementById('kb-' + letter);
  if(kb) kb.disabled = true;
  sendMsg({ type:'GUESS', payload:{ letter }});
};

function applyGuess(letter) {
  if(G.myIdx !== G.setter) return;
  if(G.guessed.includes(letter) || G.wrong.includes(letter)) return;

  const correct = G.word.includes(letter);
  if(correct) G.guessed = [...G.guessed, letter];
  else        G.wrong   = [...G.wrong, letter];

  const w = isWin(), l = isLose();
  if(w || l) {
    const winner = w ? G.guesser : G.setter;
    G.players[winner].score++;
    G.result = { win:w, lose:l, winner };
    G.phase = 'result';
    stopTimer();
  } else {
    startTimer();
  }

  sendMsg({ type:'STATE_UPDATE', payload:{
    guessed: G.guessed, wrong: G.wrong, phase: G.phase,
    score0: G.players[0].score, score1: G.players[1].score, result: G.result
  }});

  renderGame();
  if(G.phase === 'result' && G.result) setTimeout(() => showResult(G.result), 500);
}

window.selTimer = function(btn, secs) {
  chosenTimer = secs;
  document.querySelectorAll('.timer-btn').forEach(b => b.classList.remove('sel'));
  btn.classList.add('sel');
};

window.nextRound = function() {
  stopTimer();
  const newSetter = 1 - G.setter;
  G.round++; G.setter = newSetter; G.guesser = 1 - newSetter;
  G.word=''; G.hint=''; G.guessed=[]; G.wrong=[]; G.result=null;
  sendMsg({ type:'NEXT_ROUND', payload:{
    round: G.round, setter: G.setter,
    score0: G.players[0].score, score1: G.players[1].score
  }});
  if(G.myIdx === G.setter) startSetup();
  else showWaitingForWord();
};

window.leaveRoom = function() {
  stopTimer(); stopPolling();
  if(roomCode) fetch(SIGNAL_URL + '?a=delete&r=' + roomCode).catch(()=>{});
  roomCode = null; lastMsgTs = 0;
  G = { myIdx:0, players:[{name:'',score:0,av:''},{name:'',score:0,av:''}],
    round:1, word:'', hint:'', guessed:[], wrong:[], setter:0, guesser:1,
    phase:'lobby', result:null, timerSecs:0 };
  showScreen('lobby');
};

// ══════════════════════════════
//  TEMPORIZADOR
// ══════════════════════════════
function startTimer() {
  stopTimer();
  if(!G.timerSecs || G.phase !== 'playing') return;
  timerLeft = G.timerSecs;
  updateTimerUI();
  document.getElementById('timer-wrap')?.classList.remove('off');
  timerInterval = setInterval(() => {
    timerLeft--;
    updateTimerUI();
    if(timerLeft <= 0) {
      stopTimer();
      if(G.myIdx === G.guesser && G.phase === 'playing') {
        toast('⏱ Time is up! A penalty is added.','err');
        sendMsg({ type:'GUESS', payload:{ letter:'⏱' }});
      }
    }
  }, 1000);
}

function stopTimer() {
  if(timerInterval) { clearInterval(timerInterval); timerInterval = null; }
  document.getElementById('timer-wrap')?.classList.add('off');
  const num = document.getElementById('timer-num');
  if(num) { num.textContent='—'; num.classList.remove('urgent'); }
  const arc = document.getElementById('timer-arc');
  if(arc) { arc.style.strokeDashoffset='0'; arc.classList.remove('urgent'); }
}

function updateTimerUI() {
  if(!G.timerSecs) return;
  const num = document.getElementById('timer-num');
  const arc = document.getElementById('timer-arc');
  if(!num || !arc) return;
  const r = 19, circum = 2 * Math.PI * r;
  const offset = circum * (1 - timerLeft / G.timerSecs);
  const urgent = timerLeft <= 10;
  arc.style.strokeDasharray  = circum;
  arc.style.strokeDashoffset = offset;
  num.textContent = timerLeft;
  num.classList.toggle('urgent', urgent);
  arc.classList.toggle('urgent', urgent);
  document.getElementById('timer-wrap')?.classList.remove('off');
}

// ══════════════════════════════
//  RENDER
// ══════════════════════════════
function isWin()  { return G.word.replace(/ /g,'').split('').every(l => G.guessed.includes(l)); }
function isLose() { return G.wrong.length >= MAX_WRONG; }
function renderGame() { rWord(); rGallows(); rKbd(); rWrong(); rHint(); rScores(); rTurn(); }

function rWord() {
  const c = document.getElementById('word-row');
  if(!c || !G.word) return;
  const prev = new Set([...c.querySelectorAll('.lb.rv')].map(e => e.dataset.i));
  c.innerHTML = '';

  const words = G.word.split(' ');
  let globalIdx = 0;

  const maxLen = Math.max(...words.map(w => w.length));
  const boxW = maxLen >= 12 ? 22 : maxLen >= 9 ? 26 : 32;
  const boxH = maxLen >= 12 ? 30 : maxLen >= 9 ? 36 : 42;
  const boxF = maxLen >= 12 ? 16 : maxLen >= 9 ? 19 : 24;

  words.forEach((word, wi) => {
    const group = document.createElement('div');
    group.className = 'word-group';

    word.split('').forEach(ch => {
      const d = document.createElement('div');
      d.className = 'lb';
      d.dataset.i = globalIdx;
      d.style.width  = boxW + 'px';
      d.style.height = boxH + 'px';
      d.style.fontSize = boxF + 'px';
      const show = G.guessed.includes(ch) || G.phase === 'result';
      if(show) {
        d.textContent = ch;
        d.classList.add('rv');
        if(!prev.has(String(globalIdx))) d.classList.add('pp');
      }
      group.appendChild(d);
      globalIdx++;
    });

    c.appendChild(group);

    if(wi < words.length - 1) {
      const sp = document.createElement('div');
      sp.className = 'lb sp';
      sp.style.width = Math.max(10, boxW * 0.5) + 'px';
      c.appendChild(sp);
      globalIdx++;
    }
  });
}

function rGallows() {
  const n = G.wrong.length;
  PARTS.forEach((id,i) => document.getElementById(id)?.classList.toggle('on', i < n));
  FACE.forEach(id => document.getElementById(id)?.classList.toggle('on', n >= MAX_WRONG));
}

function rKbd() {
  const c = document.getElementById('kbd'); if(!c) return; c.innerHTML='';
  const canPlay = G.myIdx===G.guesser && G.phase==='playing' && !isWin() && !isLose();
  ROWS.forEach(row => {
    const div = document.createElement('div'); div.className='kb-row';
    row.forEach(l => {
      const b = document.createElement('button');
      b.className='k'; b.textContent=l; b.id='kb-'+l; b.onclick=()=>guessLetter(l);
      const cor = G.guessed.includes(l) && G.word.includes(l);
      const wr  = G.wrong.includes(l);
      if(cor)        { b.classList.add('kr'); b.disabled=true; }
      else if(wr)    { b.classList.add('kw'); b.disabled=true; }
      else if(!canPlay) b.disabled=true;
      div.appendChild(b);
    });
    c.appendChild(div);
  });
}

function rWrong() {
  const c = document.getElementById('wrow'); if(!c) return;
  c.innerHTML='';
  G.wrong.forEach(l => {
    const s = document.createElement('span'); s.className='wl'; s.textContent=l; c.appendChild(s);
  });
  document.getElementById('wcount').textContent = G.wrong.length;
}

function rHint() {
  const b = document.getElementById('hbox'); if(!b) return;
  if(G.hint) { b.style.display='block'; document.getElementById('htxt').textContent=G.hint; }
  else b.style.display='none';
}

function rScores() {
  G.players.forEach((p,i) => {
    document.getElementById('sc'+i+'n').textContent = p.name || 'J'+(i+1);
    document.getElementById('sc'+i+'p').textContent = p.score || 0;
    const isSet=i===G.setter, isGue=i===G.guesser;
    const tag = isSet?(i===G.myIdx?'[you] sets':'sets'):isGue?(i===G.myIdx?'[you] guess':'guesses'):'';
    document.getElementById('sc'+i+'r').textContent = tag;
    const sc = document.getElementById('sc'+i);
    sc.classList.toggle('gsr', isGue); sc.classList.toggle('str', isSet && !isGue);
  });
  document.getElementById('rnd-lbl').textContent = 'ROUND ' + G.round;
}

function rTurn() {
  const b = document.getElementById('turn-b'); if(!b) return;
  const w=isWin(), l=isLose();
  if(w) { b.textContent='Guessed it! 🎉'; b.className='tbadge wb'; return; }
  if(l) { b.textContent='Hanged! 💀';  b.className='tbadge lb'; return; }
  if(G.myIdx===G.setter)       { b.textContent=(G.players[G.guesser]?.name||'Opponent')+' is guessing...'; b.className='tbadge'; }
  else if(G.myIdx===G.guesser) { b.textContent='Your turn!'; b.className='tbadge mt'; }
  else                          { b.textContent='Waiting...'; b.className='tbadge'; }
}

function showResult(res) {
  const {win, winner} = res;
  const mine=winner===G.myIdx, asG=G.myIdx===G.guesser;
  let title, cls;
  if(win) {
    if(asG&&mine)       { title='You got it! 🎉';      cls='rw'; }
    else if(!asG&&mine) { title='Your word survived! 💀'; cls='rw'; }
    else if(asG&&!mine) { title='They beat you! 😤';          cls='rl'; }
    else                { title='Your opponent guessed it! 😤'; cls='rl'; }
  } else {
    if(G.myIdx===G.setter) { title='¡Tu palabra sobrevivió! 💀'; cls='rw'; }
    else                   { title='Hanged! 💀';              cls='rl'; }
  }
  document.getElementById('res-title').textContent = title;
  document.getElementById('res-title').className = 'rt ' + cls;
  document.getElementById('res-word').textContent = G.word;
  document.getElementById('fin-row').innerHTML = G.players.map((p,i) =>
    `<div class="fcard ${i===winner?'win':''}">
      <div class="fav">${p.av||'👤'}</div>
      <div class="fn">${p.name||'J'+(i+1)}</div>
      <div class="fps">${p.score||0}</div>
    </div>`
  ).join('<div class="fin-vs">VS</div>');
  showScreen('result');
}

// ══════════════════════════════
//  UTILS
// ══════════════════════════════
function curScreen() { return document.querySelector('.screen.active')?.id || null; }

function showScreen(id) {
  document.querySelectorAll('.screen').forEach(s => s.classList.remove('active'));
  document.getElementById(id)?.classList.add('active');
}

function setConn(state, txt) {
  const d=document.getElementById('cdot'), t=document.getElementById('ctxt');
  if(!d||!t) return;
  d.className='dot '+state; t.textContent=txt;
}

function updateSlots() {
  [0,1].forEach(i => {
    const sl=document.getElementById('slot-'+i); if(!sl) return;
    const p=G.players[i];
    if(p?.name) {
      sl.classList.add('filled'); sl.classList.toggle('me', i===G.myIdx);
      sl.querySelector('.p-av').textContent = p.av || AVATARS[i];
      sl.querySelector('.p-nm').textContent = p.name;
      sl.querySelector('.p-tg').textContent = i===G.myIdx ? '← you' : 'opponent';
    } else {
      sl.classList.remove('filled','me');
      sl.querySelector('.p-av').textContent='👤';
      sl.querySelector('.p-nm').textContent='Waiting...';
      sl.querySelector('.p-tg').textContent='empty';
    }
  });
}

window.copyCode = function() {
  navigator.clipboard?.writeText(roomCode||'')
    .then(()=>toast('Code copied ✓','ok'))
    .catch(()=>toast(roomCode||'','ok'));
};

function genCode() {
  const c='ABCDEFGHJKLMNPQRSTUVWXYZ2345679';
  let s=''; for(let i=0;i<6;i++) s+=c[Math.floor(Math.random()*c.length)]; return s;
}

let _tt;
function toast(msg, type='') {
  const el=document.getElementById('toast');
  el.textContent=msg; el.className='toast show'+(type?' '+type:'');
  clearTimeout(_tt); _tt=setTimeout(()=>el.classList.remove('show'),3000);
}

document.addEventListener('keydown', e => {
  if(curScreen()!=='game') return;
  const k=e.key.toUpperCase();
  if('QWERTYUIOPASDFGHJKLZXCVBNMÑÜ0123456789'.includes(k)) guessLetter(k);
});

// INIT
const sn = localStorage.getItem('ahorcado_name');
if(sn) document.getElementById('inp-name').value = sn;
document.getElementById('inp-name').addEventListener('input', function() {
  localStorage.setItem('ahorcado_name', this.value);
});
document.getElementById('inp-code').addEventListener('input', function() {
  this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g,'');
});
document.getElementById('inp-word').addEventListener('input', function() {
  this.value = this.value.toUpperCase().replace(/[^A-ZÁÉÍÓÚÜÑ0-9 ]/g,'');
  document.getElementById('char-c').textContent = this.value.length + ' / 24';
});
</script>
</body>
</html>
