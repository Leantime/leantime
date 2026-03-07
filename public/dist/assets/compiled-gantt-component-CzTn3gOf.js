function Qe(e,t){const s=t.getTime()-e.getTime();return Math.round(s/864e5)}function xe(e,t){const s=new Date(e);return s.setDate(s.getDate()+t),s}function Ve(e){const t=e.getDay();return t===0||t===6}function Pe(e,t){return Math.round(e/t)}var Je=["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"];function Me(e,t=!1){return t?["January","February","March","April","May","June","July","August","September","October","November","December"][e]:Je[e]}var Ze={name:"today-marker",type:"free",init(e){let t=null;e.on("afterRender",(s,n,a)=>{const o=s.querySelector(".emboss-body");if(!o)return;const i=Qe(n.startDate,new Date),r=i*n.dayWidth;if(i<0||i>n.totalDays){t&&(t.remove(),t=null);return}t||(t=document.createElement("div"),t.className="emboss-today-col",t.innerHTML=`
          <div class="emboss-today-glow"></div>
          <div class="emboss-today-line"></div>
          <div class="emboss-today-ring"></div>
          <div class="emboss-today-dot"></div>
          <div class="emboss-today-label">Today</div>
        `),t.style.cssText=`left:${r-20}px;width:40px;height:${o.scrollHeight}px;`,(!t.parentElement||t.parentElement!==o)&&o.appendChild(t)})},styles:`
    .emboss-today-col { position: absolute; top: 0; pointer-events: none; }
    .emboss-today-glow { position: absolute; top: 0; bottom: 0; left: 0; right: 0; background-color: var(--emboss-today, #ef4444); opacity: 0.02; z-index: 0; }
    .emboss-today-line { position: absolute; top: 0; bottom: 0; left: 50%; width: 1.5px; background-color: var(--emboss-today, #ef4444); opacity: 0.5; transform: translateX(-50%); z-index: 20; }
    .emboss-today-dot { position: absolute; top: 6px; left: 50%; width: 8px; height: 8px; background-color: var(--emboss-today, #ef4444); border-radius: 50%; transform: translate(-50%, -50%); z-index: 25; }
    .emboss-today-ring { position: absolute; top: 6px; left: 50%; width: 16px; height: 16px; border-radius: 50%; background-color: rgba(239,68,68, 0.15); z-index: 24; animation: emboss-pulse 2s ease-in-out infinite; transform: translate(-50%, -50%); }
    .emboss-today-label { position: absolute; top: -14px; left: 50%; transform: translateX(-50%); font-size: 8px; font-weight: 700; color: var(--emboss-today, #ef4444); letter-spacing: 0.6px; text-transform: uppercase; font-family: monospace; z-index: 25; white-space: nowrap; }
    .emboss-dark .emboss-today-line { background-color: var(--emboss-today, #f87171); }
    .emboss-dark .emboss-today-dot { background-color: var(--emboss-today, #f87171); }
    .emboss-dark .emboss-today-glow { background-color: var(--emboss-today, #f87171); }
    .emboss-dark .emboss-today-label { color: var(--emboss-today, #f87171); }
    @keyframes emboss-pulse { 0%, 100% { transform: translate(-50%, -50%) scale(1); opacity: 0.15; } 50% { transform: translate(-50%, -50%) scale(1.6); opacity: 0.05; } }
    /* Dense mode: simplified today marker — just thin line + small dot */
    .emboss-dense .emboss-today-ring { display: none !important; }
    .emboss-dense .emboss-today-label { display: none !important; }
    .emboss-dense .emboss-today-glow { display: none; }
    .emboss-dense .emboss-today-dot { width: 6px; height: 6px; }
    .emboss-dense .emboss-today-line { width: 1px; }
    /* Presentation mode: more prominent */
    .emboss-presentation .emboss-today-line { width: 2px; }
    .emboss-presentation .emboss-today-dot { width: 10px; height: 10px; }
    .emboss-presentation .emboss-today-ring { width: 20px; height: 20px; }
    .emboss-presentation .emboss-today-label { font-size: 9px; }
  `},We=["#3b82f6","#8b5cf6","#ec4899","#f59e0b","#10b981","#ef4444","#06b6d4","#f97316"];function et(e){let t=0;for(const s of e)t=(t<<5)-t+s.charCodeAt(0)|0;return We[Math.abs(t)%We.length]}function tt(e){const t=e.trim().split(/\s+/);return t.length===1?t[0][0].toUpperCase():(t[0][0]+t[t.length-1][0]).toUpperCase()}function st(e,t){const s=t?e.assigneeColor||et(e.assignee):"#9ca3af",n=tt(e.assignee);return`<div style="width:22px;height:22px;border-radius:50%;background:${s};position:relative;flex-shrink:0;overflow:hidden"><span style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:700;color:#ffffff;text-shadow:0 1px 2px rgba(0,0,0,0.4),0 0 4px rgba(0,0,0,0.2);letter-spacing:0.5px">${n}</span><span style="position:absolute;top:0;left:0;right:0;height:50%;border-radius:11px 11px 0 0;background:linear-gradient(180deg,rgba(255,255,255,0.35) 0%,transparent 100%);pointer-events:none"></span></div>`}function nt(e,t,s){const n=xe(e,t),a=xe(e,t+s-1),o={month:"short",day:"numeric"};return`${n.toLocaleDateString("en-US",o)} – ${a.toLocaleDateString("en-US",o)}`}function at(e){return e.charAt(0).toUpperCase()+e.slice(1)}function ot(e){return e==="active"?"var(--emboss-ink-3)":e==="done"?"var(--emboss-ink-4)":"var(--emboss-ink-5)"}var it={name:"tooltips",type:"free",init(e){let t=null,s=null,n=null,a=null,o=!1;function i(){return t||(t=document.createElement("div"),t.className="emboss-tip",document.body.appendChild(t)),t}function r(c,y,C){const g=i();g.classList.toggle("emboss-tip-dark",e.state.theme==="dark");const{scale:D}=e.state,N=c.duration>0?nt(D.startDate,c.start,c.duration):"",A=c.type!=="phase"?`<div class="emboss-tip-bar"><div class="emboss-tip-fill" style="width:${c.progress}%"></div></div>`:"";g.innerHTML=`
        ${c.phaseName?`<div class="emboss-tip-phase">${c.phaseName}</div>`:""}
        <div class="emboss-tip-name">${c.name}</div>
        <div class="emboss-tip-row">
          <span class="emboss-tip-dot" style="background:${ot(c.status)}"></span>
          <span>${at(c.status)}</span>
          ${c.progress>0&&c.progress<100?`<span class="emboss-tip-pct">${c.progress}%</span>`:""}
        </div>
        ${N?`<div class="emboss-tip-range">${N}</div>`:""}
        ${A}
        ${c.assignee?`<div class="emboss-tip-assignee">${st(c,o)}<span>${c.assignee}</span></div>`:""}
      `,p(g,y,C),g.classList.add("show")}function p(c,y,C){let g=y+8,D=C+16;const N=240,A=c.offsetHeight||80;g+N>window.innerWidth-8&&(g=y-N-8),D+A>window.innerHeight-8&&(D=C-A-8),c.style.left=`${g}px`,c.style.top=`${D}px`}function h(){t&&t.classList.remove("show"),a=null}e.on("onHover",c=>{n&&(clearTimeout(n),n=null),s&&(clearTimeout(s),s=null),c&&c.type!=="phase"&&e.state.density!=="presentation"?(a=c,s=setTimeout(()=>{a&&r(a,f,b)},200)):n=setTimeout(h,80)});let f=0,b=0;e.on("afterRender",c=>{o=c.classList.contains("emboss-vivid");const y=c.querySelector(".emboss-bars");!y||y.__embossTipWired||(y.__embossTipWired=!0,y.addEventListener("mousemove",C=>{f=C.clientX,b=C.clientY,t&&t.classList.contains("show")&&a&&p(t,f,b)}))})},styles:`
    .emboss-tip { position: fixed; z-index: 1000; pointer-events: none; background-color: #1a1d23; color: #fff; border-radius: 10px; padding: 10px 14px; font-size: 11px; line-height: 1.5; box-shadow: 0 4px 20px rgba(0,0,0,0.15); min-width: 200px; max-width: 240px; opacity: 0; transform: translateY(4px); transition: opacity 0.15s, transform 0.15s; }
    .emboss-tip.show { opacity: 1; transform: translateY(0); }
    .emboss-tip.emboss-tip-dark { background-color: #e5e7eb; color: #1a1d23; box-shadow: 0 4px 20px rgba(0,0,0,0.4); }
    .emboss-tip-phase { font-size: 9px; text-transform: uppercase; letter-spacing: 0.4px; color: rgba(255,255,255,0.6); margin-bottom: 2px; }
    .emboss-tip-name { font-weight: 600; font-size: 12px; margin-bottom: 4px; }
    .emboss-tip-row { display: flex; align-items: center; gap: 5px; margin-bottom: 3px; }
    .emboss-tip-dot { width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0; }
    .emboss-tip-pct { color: rgba(255,255,255,0.6); margin-left: auto; }
    .emboss-tip-range { color: rgba(255,255,255,0.6); font-size: 10px; margin-bottom: 4px; }
    .emboss-tip-bar { height: 3px; background: rgba(255,255,255,0.15); border-radius: 2px; margin-top: 4px; overflow: hidden; }
    .emboss-tip-fill { height: 100%; background: rgba(255,255,255,0.5); border-radius: 2px; }
    .emboss-tip-dark .emboss-tip-phase,
    .emboss-tip-dark .emboss-tip-pct,
    .emboss-tip-dark .emboss-tip-range,
    .emboss-tip-dark .emboss-tip-assignee { color: #4b5563; }
    .emboss-tip-dark .emboss-tip-bar { background: rgba(0,0,0,0.08); }
    .emboss-tip-dark .emboss-tip-fill { background: rgba(0,0,0,0.2); }
    .emboss-tip-assignee { margin-top: 8px; padding-top: 8px; border-top: 1px solid rgba(255,255,255,0.08); font-size: 12px; color: rgba(255,255,255,0.6); display: flex; align-items: center; gap: 10px; }
    .emboss-tip-dark .emboss-tip-assignee { border-top-color: rgba(0,0,0,0.08); }
  `};function rt(e,t){return e.status==="done"&&t.status==="done"?{stroke:"var(--emboss-ink-5)",width:1.2,dash:"",opacity:.4}:t.status==="upcoming"?{stroke:"var(--emboss-ink-4)",width:1.5,dash:"5 3",opacity:.5}:{stroke:"var(--emboss-ink-3)",width:1.5,dash:"",opacity:.65}}var _e="http://www.w3.org/2000/svg",dt={name:"dependency-arrows",type:"free",init(e){let t=null;e.on("afterRender",(s,n,a)=>{const o=s.querySelector(".emboss-body");if(!o)return;const i=a.rows.filter(f=>!f.hidden),r=new Map;i.forEach((f,b)=>r.set(f.id,b)),t||(t=document.createElementNS(_e,"svg"),t.classList.add("emboss-dep")),t.setAttribute("width",String(n.totalDays*n.dayWidth)),t.setAttribute("height",String(i.length*n.rowHeight)),t.innerHTML="";const p=12,h=4;for(const f of i){if(!f.dependencies||f.dependencies.length===0)continue;const b=r.get(f.id);if(b!==void 0)for(const c of f.dependencies){const y=r.get(c);if(y===void 0)continue;const C=i[y],g=C.start*n.dayWidth,D=y*n.rowHeight+n.rowHeight/2,N=f.start*n.dayWidth,A=b*n.rowHeight+n.rowHeight/2,M=Math.min(g,N)-p,z=rt(C,f),d=ut(g,D,N,A,M,h),l=document.createElementNS(_e,"path");l.setAttribute("d",d),l.setAttribute("fill","none"),l.setAttribute("stroke",z.stroke),l.setAttribute("stroke-width",String(z.width)),l.setAttribute("stroke-linecap","round"),l.setAttribute("stroke-linejoin","round"),z.dash&&l.setAttribute("stroke-dasharray",z.dash),l.dataset.f=C.id,l.dataset.t=f.id,l.dataset.depOpacity=String(z.opacity),l.style.opacity="0",t.appendChild(l)}}(!t.parentElement||t.parentElement!==o)&&o.appendChild(t),qe(t,a.hoveredRow)}),e.on("onHover",s=>{t&&qe(t,e.state.hoveredRow)})},styles:`
    svg.emboss-dep { position: absolute; top: 0; left: 0; pointer-events: none; z-index: 15; overflow: visible; }
    svg.emboss-dep path { transition: opacity 0.2s; }
  `};function qe(e,t){const s=e.querySelectorAll("path");for(const n of s){const a=n.dataset;t&&(a.f===t||a.t===t)?n.style.opacity=a.depOpacity||"0.65":n.style.opacity="0"}}function ut(e,t,s,n,a,o){if(Math.abs(t-n)<1)return`M ${e} ${t} H ${a} H ${s}`;const i=n>t,r=Math.abs(e-a),p=Math.abs(n-t),h=Math.min(o,r,p/2),f=i?1:-1;return[`M ${e} ${t}`,`H ${a+h}`,`Q ${a} ${t} ${a} ${t+f*h}`,`V ${n-f*h}`,`Q ${a} ${n} ${a+h} ${n}`,`H ${s}`].join(" ")}var Oe={working:{day:{dayWidth:44,rowHeight:44,barHeight:26,barRadius:13,labelSize:11.5},week:{dayWidth:32,rowHeight:44,barHeight:26,barRadius:13,labelSize:11.5},month:{dayWidth:12,rowHeight:40,barHeight:22,barRadius:11,labelSize:11.5},quarter:{dayWidth:7,rowHeight:40,barHeight:22,barRadius:11,labelSize:11.5}},presentation:{day:{dayWidth:48,rowHeight:60,barHeight:34,barRadius:17,labelSize:13},week:{dayWidth:36,rowHeight:60,barHeight:34,barRadius:17,labelSize:13},month:{dayWidth:14,rowHeight:56,barHeight:30,barRadius:15,labelSize:12.5},quarter:{dayWidth:8,rowHeight:56,barHeight:30,barRadius:15,labelSize:12.5}},dense:{day:{dayWidth:44,rowHeight:30,barHeight:18,barRadius:9,labelSize:10},week:{dayWidth:32,rowHeight:30,barHeight:18,barRadius:9,labelSize:10},month:{dayWidth:10,rowHeight:28,barHeight:16,barRadius:8,labelSize:9.5},quarter:{dayWidth:5,rowHeight:28,barHeight:16,barRadius:8,labelSize:9.5}}},lt={day:30,week:42,month:90,quarter:180};function ye(e,t,s,n){const a=Oe[t]?.[e]??Oe.working.week,o=s.reduce((r,p)=>Math.max(r,p.start+p.duration),0),i=lt[e]??30;return{...a,totalDays:Math.max(o+14,i),startDate:n}}function ct(e,t){return{rows:e,view:"week",density:"working",theme:"grayscale",collapsed:{},selected:null,hoveredRow:null,moveDependencies:!1,settings:{markWeekends:!1,excludeWeekends:!1,holidays:[],ignoredDays:[]},scale:ye("week","working",e,t)}}function ze(e){const{rows:t,collapsed:s}=e;for(const n of t){if(!n.parentId){n.hidden=!1;continue}let a=n.parentId,o=!1;for(;a;){if(s[a]){o=!0;break}a=t.find(r=>r.id===a)?.parentId??null}n.hidden=o}}function pt(e,t,s,n){return e.type==="phase"?gt(e,t,s,n):mt(e,t,s,n)}function mt(e,t,s,n){const a=n?.classList.contains("emboss-vivid")??!1,o=s.density==="dense",i=s.density==="presentation",r=e.start*t.dayWidth,p=o?t.dayWidth<=5?6:t.dayWidth<=10?8:t.barHeight:t.barHeight,h=Math.max(e.duration*t.dayWidth,p),f=Math.round((t.rowHeight-t.barHeight)/2),b=t.barRadius,c=document.createElement("div");c.className="emboss-bar",c.dataset.id=e.id,c.dataset.status=e.status,c.dataset.type=e.type;const y=e.status==="done"?"opacity:var(--emboss-opacity-done,0.45);":"";c.style.cssText=`left:${r}px;width:${h}px;top:${f}px;height:${t.barHeight}px;border-radius:${b}px;${y}`;const C=document.createElement("div");C.className="emboss-bar-track",c.appendChild(C);const g=Math.max(0,Math.min(100,e.progress)),D=e.status==="upcoming"&&g===0,N=D?h:Math.max(g>0?14:0,h*g/100),A=g>=100||D?`${b}px`:`${b}px 0 0 ${b}px`,M=document.createElement("div");M.className="emboss-bar-fill",M.style.cssText=`width:${N}px;border-radius:${A};`;const z=a?ft(e,s):null;if(z&&(M.style.backgroundImage=ht(z.color,e.status),e.type==="subtask"&&(M.style.opacity="0.7")),c.appendChild(M),!o&&g>0&&g<100){const l=document.createElement("div");l.className="emboss-bar-marker",l.style.left=`${N-6}px`,c.appendChild(l)}const d=document.createElement("div");if(d.className="emboss-bar-label",d.style.cssText=`font-size:${t.labelSize}px;height:${t.barHeight}px;line-height:${t.barHeight}px;`,o)n?.classList.contains("emboss-has-sidebar")??!1?h>50?(d.textContent=e.name,d.classList.add("emboss-bar-label-inside")):d.style.display="none":(d.style.display="none",c.addEventListener("mouseenter",()=>{const u=document.createElement("div");u.className="emboss-dense-tag",u.textContent=e.name,u.style.left=`${r}px`,u.style.top=`${f-18}px`,c._denseTag=u,c.parentElement?.appendChild(u)}),c.addEventListener("mouseleave",()=>{c._denseTag?.remove(),c._denseTag=null}));else if(i){const l=g>0&&g<100?` · ${g}%`:"";d.textContent=e.name+l,h>50?(d.classList.add("emboss-bar-label-inside"),d.style.fontWeight="600",D&&d.classList.add("emboss-bar-label-upcoming")):d.classList.add("emboss-bar-label-outside")}else{const l=g>0&&g<100?` ${g}%`:"";d.textContent=e.name+l,h<=70?d.classList.add("emboss-bar-label-outside"):(d.classList.add("emboss-bar-label-inside"),D&&d.classList.add("emboss-bar-label-upcoming"))}if(c.appendChild(d),o&&g>0&&g<100&&t.dayWidth>=8){const l=document.createElement("div");l.className="emboss-minibar",l.style.width=`${h*g/100}px`,z&&(l.style.background=z.color),c.appendChild(l)}if(s.density==="working"){const l=document.createElement("div");l.className="emboss-bar-handle emboss-bar-handle-left",c.appendChild(l);const u=document.createElement("div");u.className="emboss-bar-handle emboss-bar-handle-right",c.appendChild(u)}return c}var Le=["#3b82f6","#8b5cf6","#ec4899","#f59e0b","#10b981","#ef4444","#06b6d4","#f97316"];function bt(e){const t=parseInt(e.replace("#",""),16),s=(t>>16)/255,n=(t>>8&255)/255,a=(t&255)/255,o=Math.max(s,n,a),i=Math.min(s,n,a),r=(o+i)/2;if(o===i)return{h:0,s:0,l:r*100};const p=o-i,h=r>.5?p/(2-o-i):p/(o+i);let f=0;return o===s?f=((n-a)/p+(n<a?6:0))/6:o===n?f=((a-s)/p+2)/6:f=((s-n)/p+4)/6,{h:f*360,s:h*100,l:r*100}}function ke(e,t,s){t/=100,s/=100;const n=t*Math.min(s,1-s),a=o=>{const i=(o+e/30)%12,r=s-n*Math.max(Math.min(i-3,9-i,1),-1);return Math.round(255*r).toString(16).padStart(2,"0")};return`#${a(0)}${a(8)}${a(4)}`}function ht(e,t){const s=bt(e),n=(s.h+50)%360;if(t==="done"){const o=ke(s.h,s.s*.6,Math.min(s.l+15,80)),i=ke(n,s.s*.6,Math.min(s.l+15,80));return`linear-gradient(90deg, ${o}, ${i})`}if(t==="upcoming"){const o=ke(s.h,s.s*.8,Math.min(s.l+5,70)),i=ke(n,s.s*.8,Math.min(s.l+5,70));return`linear-gradient(90deg, ${o}, ${i})`}const a=ke(n,Math.min(s.s,85),Math.min(s.l+10,65));return`linear-gradient(90deg, ${e}, ${a})`}function ft(e,t){let s;if(e.type==="phase")s=e;else if(e.parentId){const o=t.rows.find(i=>i.id===e.parentId);o?.type==="phase"?s=o:o?.parentId&&(s=t.rows.find(i=>i.id===o.parentId&&i.type==="phase"))}if(!s)return null;const n=t.rows.filter(o=>o.type==="phase").indexOf(s),a=n>=0?n:0;return{color:s.phaseColor||Le[a%Le.length],idx:a}}function gt(e,t,s,n){const a=e.start*t.dayWidth,o=Math.max(e.duration*t.dayWidth,20),i=s.density==="presentation",r=i?7:5,p=i?4:3,h=Math.round((t.rowHeight-r)/2),f=n?.classList.contains("emboss-vivid")??!1,b=document.createElement("div");if(b.className="emboss-bar emboss-bar-phase",b.dataset.id=e.id,b.dataset.type="phase",b.style.cssText=`left:${a}px;width:${o}px;top:${h}px;height:${r}px;border-radius:${p}px;`,f){const y=s.rows.filter(D=>D.type==="phase").findIndex(D=>D.id===e.id),C=y>=0?y:0,g=e.phaseColor||Le[C%Le.length];b.style.background=g,b.style.opacity="0.25"}const c=document.createElement("div");return c.className="emboss-bar-label emboss-bar-label-phase",c.style.fontSize=`${t.labelSize}px`,c.textContent=e.name,b.appendChild(c),b}var vt=`
.emboss-bar {
  position: absolute;
  cursor: pointer;
  user-select: none;
  outline: none;
}
.emboss-bar:focus {
  outline: none;
}

/* Track — pill-shaped container behind fill, inherits bar's border-radius */
.emboss-bar-track {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  border-radius: inherit;
  background-color: var(--emboss-track, #e8ecf1);
  box-shadow: inset 0 1px 2px rgba(0,0,0,0.06);
}
.emboss-dark .emboss-bar-track {
  box-shadow: none;
}

.emboss-bar-fill {
  position: absolute;
  top: 0;
  left: 0;
  height: 100%;
  z-index: 1;
}

/* Glass highlight (::before) — top 2px, inset 4px, height 40% */
.emboss-bar-fill::before {
  content: '';
  position: absolute;
  top: 2px;
  left: 4px;
  right: 4px;
  height: 40%;
  border-radius: inherit;
  background: linear-gradient(180deg, rgba(255,255,255,0.5) 0%, rgba(255,255,255,0.12) 50%, transparent 100%);
  pointer-events: none;
}

/* Shadow (::after) — bottom 0, height 35%, gradient to rgba(0,0,0,0.1) */
.emboss-bar-fill::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  height: 35%;
  border-radius: inherit;
  background: linear-gradient(180deg, transparent 0%, rgba(0,0,0,0.1) 100%);
  pointer-events: none;
}

/* Status-driven fill gradients — via CSS custom properties for theme swaps */
.emboss-bar[data-status="active"] .emboss-bar-fill {
  background-image: var(--emboss-fill-active);
}
.emboss-bar[data-status="done"] .emboss-bar-fill {
  background-image: var(--emboss-fill-done);
}
.emboss-bar[data-status="upcoming"] .emboss-bar-fill {
  background-image: var(--emboss-fill-upcoming);
}

/* Opacity states — theme-responsive via CSS variables */
.emboss-bar[data-status="done"] {
  opacity: var(--emboss-opacity-done, 0.45);
  transition: opacity 0.15s;
}
.emboss-bar[data-status="done"]:hover {
  opacity: 0.65 !important;
}
.emboss-bar[data-status="upcoming"] .emboss-bar-fill {
  opacity: var(--emboss-opacity-upcoming, 0.5);
}

/* Progress marker dot — 12px outer, 6px inner, z-index 2 so labels render above */
.emboss-bar-marker {
  position: absolute;
  top: 50%;
  width: 12px;
  height: 12px;
  border-radius: 50%;
  background: #fff;
  transform: translateY(-50%);
  z-index: 2;
  cursor: ew-resize;
  transition: transform 0.15s;
}

/* Marker inner status-colored dot */
.emboss-bar-marker::before {
  content: '';
  position: absolute;
  top: 50%;
  left: 50%;
  width: 6px;
  height: 6px;
  border-radius: 50%;
  transform: translate(-50%, -50%);
}
.emboss-bar[data-status="active"] .emboss-bar-marker::before {
  background: var(--emboss-ink-3);
}
.emboss-bar[data-status="done"] .emboss-bar-marker::before {
  background: var(--emboss-ink-4);
}
.emboss-bar[data-status="upcoming"] .emboss-bar-marker::before {
  background: var(--emboss-ink-4);
}

/* Marker hover scale */
.emboss-bar:hover .emboss-bar-marker {
  transform: translateY(-50%) scale(1.15);
}

/* Labels — z-index 4 above fill and marker */
.emboss-bar-label {
  position: absolute;
  top: 0;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
  pointer-events: none;
  z-index: 4;
}

/* Inside label — white text on fill, text-shadow for contrast (§9.2) */
.emboss-bar-label-inside {
  left: 10px;
  right: 10px;
  color: #fff;
  font-weight: 500;
  text-shadow: 0 1px 2px rgba(0,0,0,.2);
}

/* Dark mode: stronger text-shadow for contrast */
.emboss-dark .emboss-bar-label-inside {
  text-shadow: 0 1px 2px rgba(0,0,0,.4);
}

/* Upcoming 0% — fill covers full width, label white at 75% opacity in both themes */
.emboss-bar-label-upcoming {
  color: #fff;
  opacity: 0.75;
  text-shadow: 0 1px 2px rgba(0,0,0,.2);
}

/* Outside label — positioned right of bar, same vertical center */
.emboss-bar-label-outside {
  left: calc(100% + 6px);
  color: var(--emboss-ink-4);
  max-width: 200px;
}
.emboss-dense .emboss-bar-label-outside { max-width: 160px; }

/* Drag handles */
.emboss-bar-handle {
  position: absolute;
  top: 50%;
  width: 3px;
  height: 55%;
  background: rgba(255,255,255,0.4);
  border-radius: 2px;
  transform: translateY(-50%);
  opacity: 0;
  transition: opacity 0.15s;
  z-index: 5;
  cursor: ew-resize;
}
.emboss-bar:hover .emboss-bar-handle {
  opacity: 0.7;
}
.emboss-bar-handle-left {
  left: 4px;
}
.emboss-bar-handle-right {
  right: 4px;
}

/* ─── Minibar (dense mode progress indicator) ─────────────────────────── */

.emboss-minibar {
  position: absolute;
  bottom: -5px;
  left: 0;
  height: 3px;
  border-radius: 1.5px;
  background: var(--emboss-ink-3);
  opacity: 0.6;
}

/* ─── Dense mode overrides ────────────────────────────────────────────── */

/* Strip glass highlight — bars become flat */
.emboss-dense .emboss-bar-fill::before { display: none !important; }
/* Thinner bottom shadow */
.emboss-dense .emboss-bar-fill::after { height: 20%; }
/* Grab entire bar body */
.emboss-dense .emboss-bar { cursor: grab; }
.emboss-dense .emboss-bar:active { cursor: grabbing; }
/* Subtle hover — opacity bump, NOT scaleY transform */
.emboss-dense .emboss-bar:hover { opacity: 0.9; transform: none !important; }
/* Hide progress marker dots — minibar replaces them */
.emboss-dense .emboss-bar-marker { display: none !important; }
/* Hide drag handles */
.emboss-dense .emboss-bar-handle { display: none; }
/* Hide phase bars — sidebar shows grouping */
.emboss-dense .emboss-bar-phase { display: none !important; }
/* With sidebar: kill outside labels — only inside or hidden */
.emboss-dense.emboss-has-sidebar .emboss-bar-label-outside { display: none !important; }
/* Dense hover name tag (no-sidebar mode) */
.emboss-dense-tag {
  position: absolute;
  padding: 2px 6px;
  border-radius: 4px;
  background: var(--emboss-surface);
  border: 1px solid var(--emboss-border);
  box-shadow: 0 2px 6px rgba(0,0,0,0.08);
  font-size: 10px;
  font-weight: 500;
  color: var(--emboss-ink-2);
  white-space: nowrap;
  pointer-events: none;
  z-index: 30;
}

/* ─── Presentation mode overrides ─────────────────────────────────────── */

/* No drag, no interaction chrome */
.emboss-presentation .emboss-bar { cursor: default; }
.emboss-presentation .emboss-bar:hover { transform: none; }
.emboss-presentation .emboss-bar-marker { cursor: default; }
/* Enhanced glass highlight */
.emboss-presentation .emboss-bar-fill::before {
  background: linear-gradient(180deg, rgba(255,255,255,0.55) 0%, rgba(255,255,255,0.15) 60%, transparent 100%);
}
/* Deeper shadow */
.emboss-presentation .emboss-bar-fill::after {
  height: 38%;
  background: linear-gradient(180deg, transparent 0%, rgba(0,0,0,0.15) 100%);
}
/* Larger progress dot */
.emboss-presentation .emboss-bar-marker { width: 14px; height: 14px; }
.emboss-presentation .emboss-bar-marker::before { width: 8px; height: 8px; }
/* Bolder inside labels */
.emboss-presentation .emboss-bar-label-inside {
  text-shadow: 0 1px 3px rgba(0,0,0,0.25);
}
/* Phase bar slightly more visible in presentation */
.emboss-presentation .emboss-bar-phase { opacity: 0.3; }

/* Phase bar — grayscale default; vivid overrides set inline */
.emboss-bar-phase {
  pointer-events: none;
  background: var(--emboss-ink-4);
  opacity: 0.25;
}

/* Phase label — below the thin bar, left-aligned */
.emboss-bar-label-phase {
  top: 100%;
  left: 0;
  margin-top: 2px;
  color: var(--emboss-ink-3);
  font-weight: 600;
  font-size: 10px;
  letter-spacing: 0.3px;
  text-transform: uppercase;
}
`;function yt(e,t){switch(t.view){case"day":return xt(e);case"week":return wt(e);case"month":return Et(e,t);case"quarter":return kt(e,t)}}function xt(e,t){const s=document.createElement("div");s.className="emboss-header-inner";const n=document.createElement("div");n.className="emboss-header-row emboss-header-row-top";const a=document.createElement("div");a.className="emboss-header-row emboss-header-row-bottom";let o=-1,i=null,r=0;for(let p=0;p<e.totalDays;p++){const h=xe(e.startDate,p),f=h.getMonth(),b=h.getFullYear();f!==o&&(i&&(i.style.width=`${r*e.dayWidth}px`),i=document.createElement("span"),i.className="emboss-header-cell emboss-header-month",i.textContent=`${Me(f)} ${b}`,n.appendChild(i),o=f,r=0),r++;const c=document.createElement("span");c.className="emboss-header-cell emboss-header-day",Ve(h)&&c.classList.add("emboss-header-weekend"),c.style.width=`${e.dayWidth}px`,c.textContent=`${h.getDate()}`,a.appendChild(c)}return i&&(i.style.width=`${r*e.dayWidth}px`),s.appendChild(n),s.appendChild(a),s}function wt(e,t){const s=document.createElement("div");s.className="emboss-header-inner";const n=document.createElement("div");n.className="emboss-header-row emboss-header-row-top";const a=document.createElement("div");a.className="emboss-header-row emboss-header-row-bottom";let o=-1,i=null,r=0;for(let p=0;p<e.totalDays;p++){const h=xe(e.startDate,p),f=h.getMonth(),b=h.getFullYear();if(f!==o&&(i&&(i.style.width=`${r*e.dayWidth}px`),i=document.createElement("span"),i.className="emboss-header-cell emboss-header-month",i.textContent=`${Me(f)} ${b}`,n.appendChild(i),o=f,r=0),r++,h.getDay()===1){const c=document.createElement("span");c.className="emboss-header-cell emboss-header-week",c.style.width=`${7*e.dayWidth}px`;const y={month:"short",day:"numeric"};c.textContent=h.toLocaleDateString("en-US",y),a.appendChild(c)}}return i&&(i.style.width=`${r*e.dayWidth}px`),s.appendChild(n),s.appendChild(a),s}function Et(e,t){const s=document.createElement("div");s.className="emboss-header-inner";const n=document.createElement("div");n.className="emboss-header-row";let a=-1,o=null,i=0;for(let r=0;r<e.totalDays;r++){const h=xe(e.startDate,r).getMonth();h!==a&&(o&&(o.style.width=`${i*e.dayWidth}px`),o=document.createElement("span"),o.className="emboss-header-cell emboss-header-month",o.textContent=Me(h,t.density==="presentation"),n.appendChild(o),a=h,i=0),i++}return o&&(o.style.width=`${i*e.dayWidth}px`),s.appendChild(n),s}function kt(e,t){const s=document.createElement("div");s.className="emboss-header-inner";const n=t.density==="dense",a=document.createElement("div");a.className="emboss-header-row emboss-header-row-top";const o=n?null:document.createElement("div");o&&(o.className="emboss-header-row emboss-header-row-bottom");let i=-1,r=-1,p=null,h=null,f=0,b=0;for(let c=0;c<e.totalDays;c++){const y=xe(e.startDate,c),C=y.getMonth(),g=y.getFullYear(),D=Math.floor(C/3);(D!==i||c===0)&&(p&&(p.style.width=`${f*e.dayWidth}px`),p=document.createElement("span"),p.className="emboss-header-cell emboss-header-quarter",p.textContent=`Q${D+1} ${g}`,a.appendChild(p),i=D,f=0),f++,o&&C!==r&&(h&&(h.style.width=`${b*e.dayWidth}px`),h=document.createElement("span"),h.className="emboss-header-cell emboss-header-month",h.textContent=Me(C),o.appendChild(h),r=C,b=0),o&&b++}return p&&(p.style.width=`${f*e.dayWidth}px`),h&&o&&(h.style.width=`${b*e.dayWidth}px`),s.appendChild(a),o&&s.appendChild(o),n&&a.classList.remove("emboss-header-row-top"),s}var Ct=`
.emboss-header {
  position: sticky;
  top: 0;
  z-index: 10;
  background: var(--emboss-surface);
  border-bottom: 1px solid var(--emboss-border);
  overflow: hidden;
  min-height: min-content;
}
.emboss-header-inner {
  display: flex;
  flex-direction: column;
}
.emboss-header-row {
  display: flex;
  align-items: center;
  height: 28px;
}
.emboss-header-cell {
  display: inline-flex;
  align-items: center;
  padding: 0 6px;
  font-size: var(--emboss-label-size);
  color: var(--emboss-ink-3);
  white-space: nowrap;
  overflow: hidden;
  box-sizing: border-box;
  flex-shrink: 0;
}
.emboss-header-row-top {
  border-bottom: 1px solid var(--emboss-border);
}
.emboss-header-month {
  font-weight: 600;
}
.emboss-header-week {
  font-size: 10px;
}
.emboss-header-day {
  justify-content: center;
  padding: 0;
}
.emboss-header-weekend {
  color: var(--emboss-ink-4);
  opacity: 0.6;
}
.emboss-header-quarter {
  font-weight: 600;
}
/* ─── Dense header ────────────────────────────────────────────────────── */
.emboss-dense .emboss-header-row { height: 24px; }
.emboss-dense .emboss-header-cell { font-size: 10px; }
.emboss-dense .emboss-header-week { font-size: 9px; }
/* ─── Presentation header ─────────────────────────────────────────────── */
.emboss-presentation .emboss-header-row { height: 32px; }
.emboss-presentation .emboss-header-cell { font-size: 13px; }
.emboss-presentation .emboss-header-week { font-size: 11px; }
`;function St(e,t,s,n){const a=document.createElement("div");a.className="emboss-grid-inner";const o=e.totalDays*e.dayWidth,i=n?n.reduce((h,f)=>h+f,0):s*e.rowHeight;for(let h=0;h<e.totalDays;h++){const f=xe(e.startDate,h),b=h*e.dayWidth;if(Ve(f)&&t.settings.markWeekends){const g=document.createElement("div");g.className="emboss-grid-weekend",g.style.cssText=`left:${b}px;width:${e.dayWidth}px;height:${i}px;`,a.appendChild(g)}let y=!1,C=!1;if(t.view==="day"?y=!0:t.view==="week"?y=f.getDay()===1:y=f.getDate()===1,t.settings.markWeekends&&f.getDay()===6&&h>0&&(C=!0),(y||C)&&h>0){const g=document.createElement("div");g.className="emboss-grid-vline",C&&!y&&g.classList.add("emboss-grid-vline-boundary"),g.style.cssText=`left:${b}px;height:${i}px;`,a.appendChild(g)}}const r=t.density==="dense";let p=0;for(let h=0;h<=s;h++){const f=n?n[h]??e.rowHeight:e.rowHeight;if(r&&h<s&&h%2===1){const b=document.createElement("div");b.className="emboss-grid-stripe",b.style.cssText=`top:${p}px;width:${o}px;height:${f}px;`,a.appendChild(b)}if(h>0){const b=document.createElement("div");b.className="emboss-grid-hline",b.style.cssText=`top:${p}px;width:${o}px;`,a.appendChild(b)}p+=f}return a}var Dt=`
.emboss-grid {
  position: absolute;
  top: 0;
  left: 0;
  pointer-events: none;
  z-index: 0;
}
.emboss-grid-inner {
  position: relative;
}
.emboss-grid-weekend {
  position: absolute;
  top: 0;
  background: rgba(0, 0, 0, 0.025);
  pointer-events: none;
}
.emboss-dark .emboss-grid-weekend {
  background: rgba(255, 255, 255, 0.025);
}
.emboss-grid-vline {
  position: absolute;
  top: 0;
  width: 1px;
  background: var(--emboss-ink);
  opacity: var(--emboss-grid-opacity);
}
.emboss-grid-vline-boundary {
  opacity: calc(var(--emboss-grid-opacity) * 1.5);
}
.emboss-grid-hline {
  position: absolute;
  left: 0;
  height: 1px;
  background: var(--emboss-ink);
  opacity: var(--emboss-grid-opacity);
}
/* Zebra stripe (dense mode) */
.emboss-grid-stripe {
  position: absolute;
  top: 0;
  background: rgba(0, 0, 0, 0.035);
  pointer-events: none;
}
.emboss-dark .emboss-grid-stripe {
  background: rgba(255, 255, 255, 0.04);
}
/* ─── Dense mode ──────────────────────────────────────────────────────── */
.emboss-dense .emboss-grid-vline { opacity: 0.02; }
.emboss-dense .emboss-grid-weekend { background: transparent; }
/* ─── Presentation mode ───────────────────────────────────────────────── */
.emboss-presentation .emboss-grid-vline { opacity: 0.015; }
.emboss-presentation .emboss-grid-hline { opacity: 0.04; }
.emboss-presentation .emboss-grid-weekend { opacity: 0.2; }
`;function $t(e,t){const s={};for(const o of t)for(const i of o.dependencies)s[i]||(s[i]=[]),s[i].push(o.id);const n=new Set,a=[e];for(;a.length;){const o=a.shift(),i=s[o]||[];for(const r of i)n.has(r)||(n.add(r),a.push(r))}return t.filter(o=>n.has(o.id))}function Lt(e,t){let s=null;function n(i){if(i.button!==0||t.getState().density==="presentation")return;const r=i.target;let p;r.classList.contains("emboss-bar-handle-left")?p="resize-left":r.classList.contains("emboss-bar-handle-right")?p="resize-right":r.classList.contains("emboss-bar-marker")?p="progress":p="move";const h=r.closest(".emboss-bar[data-id]");if(!h)return;const f=h.dataset.id,b=t.getState(),c=b.rows.find(D=>D.id===f);if(!c||c.type==="phase"||t.emit("onDragStart",c,p)===!1)return;const C=h.cloneNode(!0);C.classList.add("emboss-bar-ghost"),h.style.opacity="0.35",h.parentElement.appendChild(C);const g=[];if(p==="move"&&b.moveDependencies){const D=$t(f,b.rows);for(const N of D){if(N.type==="phase")continue;const A=e.querySelector(`.emboss-bar[data-id="${N.id}"]`);if(!A)continue;const M=A.cloneNode(!0);M.classList.add("emboss-bar-ghost"),A.style.opacity="0.35",A.parentElement.appendChild(M),g.push({row:N,ghost:M,barEl:A,originalStart:N.start})}}s={row:c,type:p,startX:i.clientX,startY:i.clientY,originalStart:c.start,originalDuration:c.duration,originalProgress:c.progress,ghost:C,barEl:h,depGhosts:g},i.preventDefault(),document.addEventListener("mousemove",a),document.addEventListener("mouseup",o)}function a(i){if(!s)return;const{row:r,type:p,startX:h,ghost:f,originalStart:b,originalDuration:c,originalProgress:y}=s,g=t.getState().scale.dayWidth,D=i.clientX-h,N=Pe(D,g);if(p==="move"){const A=(b+N)*g;f.style.left=`${Math.max(0,A)}px`;for(const M of s.depGhosts){const z=(M.originalStart+N)*g;M.ghost.style.left=`${Math.max(0,z)}px`}t.emit("onDragMove",r,{days:N})}else if(p==="resize-right"){const A=Math.max(1,c+N);f.style.width=`${A*g}px`,t.emit("onDragMove",r,{days:N})}else if(p==="resize-left"){const A=b+N,M=c-N;M>=1&&(f.style.left=`${A*g}px`,f.style.width=`${M*g}px`),t.emit("onDragMove",r,{days:N})}else if(p==="progress"){const A=c*g,M=Math.max(0,Math.min(100,Math.round(D/A*100+y))),z=f.querySelector(".emboss-bar-marker"),d=f.querySelector(".emboss-bar-fill");d&&(d.style.width=`${Math.max(14,A*M/100)}px`),z&&(z.style.left=`${Math.max(14,A*M/100)-6}px`),t.emit("onDragMove",r,{days:0,progress:M})}}function o(i){if(!s)return;const{row:r,type:p,startX:h,originalStart:f,originalDuration:b,originalProgress:c,ghost:y,barEl:C,depGhosts:g}=s,N=t.getState().scale.dayWidth,A=i.clientX-h,M=Pe(A,N),z={};if(p==="move")z.start=Math.max(0,f+M);else if(p==="resize-right")z.duration=Math.max(1,b+M);else if(p==="resize-left"){const l=b-M;l>=1&&(z.start=f+M,z.duration=l)}else if(p==="progress"){const l=b*N;z.progress=Math.max(0,Math.min(100,Math.round(A/l*100+c)))}if(t.emit("onDragEnd",r,z)!==!1&&Object.keys(z).length>0&&(t.updateRow(r.id,z),p==="move"))for(const l of g)t.updateRow(l.row.id,{start:Math.max(0,l.originalStart+M)});y.remove(),C.style.opacity="";for(const l of g)l.ghost.remove(),l.barEl.style.opacity="";s=null,document.removeEventListener("mousemove",a),document.removeEventListener("mouseup",o)}return e.addEventListener("mousedown",n),()=>{e.removeEventListener("mousedown",n),document.removeEventListener("mousemove",a),document.removeEventListener("mouseup",o)}}var Mt=`
.emboss-bar-ghost {
  opacity: 0.7;
  pointer-events: none;
  z-index: 100;
}
`,Ie=null,ve=new Set,Rt=/^EMB-([A-Z]+)-(\d{8})-([a-f0-9]+)$/i,Nt={organize:"O",columns:"C",people:"P",subtasks:"S",analyze:"A"},Tt="emboss-2026";function zt(e){let t=4294967295;for(let s=0;s<e.length;s++){t^=e.charCodeAt(s);for(let n=0;n<8;n++)t=t>>>1^(t&1?3988292384:0)}return((t^4294967295)>>>0).toString(16).padStart(8,"0")}function Ht(e,t){return zt(Tt+"-"+e.toUpperCase()+"-"+t)}function It(e){Ie=e}function He(e){if(!Ie)return ve.has(e)||(ve.add(e),console.warn(`[Emboss] The "${e}" bundle requires a license. Get one at https://emboss.dev/pricing — your chart will work fine without it, but please support the project.`)),!1;const t=Rt.exec(Ie);if(!t)return ve.has("format")||(ve.add("format"),console.warn("[Emboss] Invalid license key format. Expected EMB-{FLAGS}-{YYYYMMDD}-{checksum}.")),!1;const[,s,n]=t,a=s.toUpperCase(),o=Ht(a,n);if(t[3].toLowerCase()!==o)return ve.has("checksum")||(ve.add("checksum"),console.warn("[Emboss] Invalid license key checksum.")),!1;const i=Nt[e];if(!i)return!1;if(e==="columns")return a.includes("O");if(!a.includes(i))return!1;const r=At(n);return r&&r<new Date&&(ve.has("expired")||(ve.add("expired"),console.warn(`[Emboss] Your license key expired on ${n.slice(0,4)}-${n.slice(4,6)}-${n.slice(6,8)}. Please renew at https://emboss.dev/pricing`))),!0}function At(e){const t=parseInt(e.slice(0,4),10),s=parseInt(e.slice(4,6),10)-1,n=parseInt(e.slice(6,8),10),a=new Date(t,s,n);return isNaN(a.getTime())?null:a}var Pt=`
.emboss {
  position: relative;
  overflow: hidden;
  height: 100%;
  background: var(--emboss-bg);
  color: var(--emboss-ink);
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  font-size: var(--emboss-label-size);
  line-height: 1.4;
  -webkit-font-smoothing: antialiased;
}
.emboss-body {
  position: relative;
  overflow: auto;
}
.emboss-spacer {
  pointer-events: none;
}
.emboss-bars {
  position: absolute;
  top: 0;
  left: 0;
  z-index: 1;
}
`,Wt=class{constructor(e,t,s={}){this.extensions=[],this.listeners={},this.sidebarRenderers={},this.barRenderers={},this.headerRenderer=null,this.headerEl=null,this.bodyEl=null,this.gridEl=null,this.barsEl=null,this.spacerEl=null,this.dragCleanup=null;const n=document.querySelector(e);if(!n)throw new Error(`Emboss: no element found for "${e}"`);this.container=n,this.options=s;const a=s.startDate?new Date(s.startDate):new Date;if(a.setHours(0,0,0,0),this.state=ct(t,a),s.licenseKey&&It(s.licenseKey),s.view&&(this.state.view=s.view),s.density&&(s.density==="working"||He("organize"))&&(this.state.density=s.density),s.theme&&(this.state.theme=s.theme),s.moveDependencies&&(this.state.moveDependencies=!0),this.state.scale=ye(this.state.view,this.state.density,t,a),this.injectStyles("core",Pt+vt+Ct+Dt+Mt),s.extensions)for(const o of s.extensions)this.use(o);this.render()}get rows(){return this.state.rows}injectStyles(e,t){const s=document.createElement("style");s.textContent=t,s.dataset.emboss=e,document.head.appendChild(s)}use(e){if(!(e.type==="paid"&&e.bundle&&!He(e.bundle))){if(this.extensions.push(e),e.sidebarRenderer&&Object.assign(this.sidebarRenderers,e.sidebarRenderer),e.barRenderer&&Object.assign(this.barRenderers,e.barRenderer),e.headerRenderer&&(this.headerRenderer=e.headerRenderer),e.styles){const t=document.createElement("style");t.textContent=e.styles,t.dataset.embossExt=e.name,document.head.appendChild(t)}e.init&&e.init(this)}}remove(e){const t=this.extensions.findIndex(n=>n.name===e);if(t===-1)return;this.extensions.splice(t,1);const s=document.head.querySelector(`[data-emboss-ext="${e}"]`);s&&s.remove(),this.sidebarRenderers={},this.barRenderers={},this.headerRenderer=null;for(const n of this.extensions)n.sidebarRenderer&&Object.assign(this.sidebarRenderers,n.sidebarRenderer),n.barRenderer&&Object.assign(this.barRenderers,n.barRenderer),n.headerRenderer&&(this.headerRenderer=n.headerRenderer);this.render()}setView(e){this.state.view=e,this.state.scale=ye(e,this.state.density,this.state.rows,this.state.scale.startDate),this.emit("onViewChange",e),this.render()}setDensity(e){e!=="working"&&!He("organize")||(this.state.density=e,this.state.scale=ye(this.state.view,e,this.state.rows,this.state.scale.startDate),this.emit("onDensityChange",e),this.render())}setTheme(e){this.state.theme=e,this.container.classList.remove("emboss-grayscale","emboss-dark"),this.container.classList.add(`emboss-${e}`),this.emit("onThemeChange",e),this.render()}toggleCollapse(e){this.state.collapsed[e]=!this.state.collapsed[e],ze(this.state);const t=this.state.rows.find(s=>s.id===e);t&&this.emit("onCollapse",t,this.state.collapsed[e]),this.render()}updateRow(e,t){const s=this.state.rows.find(a=>a.id===e);!s||this.emit("onRowUpdate",s,t)===!1||(t.progress!==void 0&&!t.status&&(t.progress>=100?t.status="done":t.progress>0&&s.status!=="active"&&(t.status="active")),Object.assign(s,t),this.state.scale=ye(this.state.view,this.state.density,this.state.rows,this.state.scale.startDate),this.render())}addRow(e,t){if(t){const s=this.state.rows.findIndex(n=>n.id===t);s>=0?this.state.rows.splice(s+1,0,e):this.state.rows.push(e)}else this.state.rows.push(e);ze(this.state),this.state.scale=ye(this.state.view,this.state.density,this.state.rows,this.state.scale.startDate),this.render()}removeRow(e){this.state.rows=this.state.rows.filter(t=>t.id!==e);for(const t of this.state.rows)t.children&&(t.children=t.children.filter(s=>s!==e));ze(this.state),this.state.scale=ye(this.state.view,this.state.density,this.state.rows,this.state.scale.startDate),this.render()}on(e,t){this.listeners[e]||(this.listeners[e]=[]),this.listeners[e].push(t)}emit(e,...t){for(const s of this.extensions){const n=s.handlers?.[e];if(n&&n(...t)===!1)return!1}for(const s of this.listeners[e]??[])if(s(...t)===!1)return!1}render(){let e=this.state.rows;for(const y of this.extensions)y.enrichRows&&(e=y.enrichRows(e,this.state));this.emit("onBeforeRender",e,this.state),this.headerEl||this.createSkeleton();const t=e.filter(y=>!y.hidden),{scale:s}=this.state,n=this.state.density==="presentation",a=n?32:s.rowHeight,o=y=>y.type==="phase"?a:s.rowHeight,i=s.totalDays*s.dayWidth,r=t.reduce((y,C)=>y+o(C),0);this.container.dataset.density=this.state.density,this.container.classList.toggle("emboss-dense",this.state.density==="dense"),this.container.classList.toggle("emboss-presentation",this.state.density==="presentation"),this.container.classList.contains(`emboss-${this.state.theme}`)||(this.container.classList.remove("emboss-grayscale","emboss-dark"),this.container.classList.add(`emboss-${this.state.theme}`));const p=this.headerRenderer?this.headerRenderer(s,this.state):yt(s,this.state);this.headerEl.innerHTML="",this.headerEl.appendChild(p),p.style.minWidth=`${i}px`;const h=t.map(y=>o(y)),f=St(s,this.state,t.length,h);this.gridEl.innerHTML="",this.gridEl.appendChild(f);const b=document.createDocumentFragment();let c=0;t.forEach(y=>{const C=this.barRenderers[y.type],g=C?C(y,s,this.state,this.container):pt(y,s,this.state,this.container),D=o(y);if(y.type==="phase"){const N=n?7:5;g.style.top=`${c+Math.round((D-N)/2)}px`}else{const N=Math.round((D-s.barHeight)/2);g.style.top=`${c+N}px`}c+=D,b.appendChild(g)}),this.barsEl.innerHTML="",this.barsEl.appendChild(b),this.spacerEl.style.width=`${i}px`,this.spacerEl.style.height=`${r}px`,this.barsEl.style.width=`${i}px`,this.barsEl.style.height=`${r}px`,this.gridEl.style.width=`${i}px`,this.gridEl.style.height=`${r}px`,this.emit("afterRender",this.container,s,this.state)}createSkeleton(){this.container.classList.add("emboss"),this.headerEl=document.createElement("div"),this.headerEl.className="emboss-header",this.bodyEl=document.createElement("div"),this.bodyEl.className="emboss-body",this.gridEl=document.createElement("div"),this.gridEl.className="emboss-grid",this.barsEl=document.createElement("div"),this.barsEl.className="emboss-bars",this.spacerEl=document.createElement("div"),this.spacerEl.className="emboss-spacer",this.bodyEl.appendChild(this.spacerEl),this.bodyEl.appendChild(this.gridEl),this.bodyEl.appendChild(this.barsEl),this.container.innerHTML="",this.container.appendChild(this.headerEl),this.container.appendChild(this.bodyEl),this.bodyEl.addEventListener("scroll",()=>{this.headerEl.scrollLeft=this.bodyEl.scrollLeft}),this.dragCleanup=Lt(this.barsEl,{emit:(e,...t)=>this.emit(e,...t),updateRow:(e,t)=>this.updateRow(e,t),getState:()=>this.state}),this.barsEl.addEventListener("mouseover",e=>{const t=e.target.closest(".emboss-bar[data-id]");if(t){const s=t.dataset.id,n=this.state.rows.find(a=>a.id===s)??null;n&&this.state.hoveredRow!==n.id&&(this.state.hoveredRow=n.id,this.emit("onHover",n))}}),this.barsEl.addEventListener("mouseout",e=>{const t=e.target.closest(".emboss-bar[data-id]"),n=e.relatedTarget?.closest(".emboss-bar[data-id]");t&&!n&&(this.state.hoveredRow=null,this.emit("onHover",null))}),this.barsEl.addEventListener("click",e=>{const t=e.target.closest(".emboss-bar[data-id]");if(t){const s=t.dataset.id,n=this.state.rows.find(a=>a.id===s);n&&(this.state.selected=n.id,this.emit("onClick",n,e))}})}destroy(){this.dragCleanup&&this.dragCleanup(),document.querySelectorAll("[data-emboss],[data-emboss-ext]").forEach(e=>e.remove()),this.container.innerHTML="",this.container.classList.remove("emboss"),this.headerEl=null,this.bodyEl=null,this.gridEl=null,this.barsEl=null,this.spacerEl=null,this.listeners={}}},Ae=["#3b82f6","#8b5cf6","#ec4899","#f59e0b","#10b981","#ef4444","#06b6d4","#f97316"];function ge(e,t){let s;if(e.type==="phase")s=e;else if(e.parentId){const a=t.find(o=>o.id===e.parentId);a?.type==="phase"?s=a:a?.parentId&&(s=t.find(o=>o.id===a.parentId&&o.type==="phase"))}if(!s)return null;if(s.phaseColor)return s.phaseColor;const n=t.filter(a=>a.type==="phase").indexOf(s);return Ae[(n>=0?n:0)%Ae.length]}function Ce(e,t){return e.type==="phase"&&t.density==="presentation"?32:t.scale.rowHeight}var Be=["#3b82f6","#8b5cf6","#ec4899","#f59e0b","#10b981","#ef4444","#06b6d4","#f97316"];function _t(e){let t=0;for(const s of e)t=(t<<5)-t+s.charCodeAt(0)|0;return Be[Math.abs(t)%Be.length]}function qt(e){const t=e.trim().split(/\s+/);return t.length===1?t[0][0].toUpperCase():(t[0][0]+t[t.length-1][0]).toUpperCase()}function Ge(e,t,s=22){const n=document.createElement("div");if(n.className=s===18?"emboss-avatar emboss-avatar-sm":"emboss-avatar",t){const o=e.assigneeColor||(e.assignee?_t(e.assignee):"");o&&(n.style.background=o)}const a=document.createElement("span");return a.className="emboss-avatar-initials",a.textContent=qt(e.assignee||""),n.appendChild(a),n}function Re(){const e=document.createElement("span");return e.className="emboss-sidebar-grip",e.innerHTML='<svg width="6" height="10" viewBox="0 0 6 10"><circle cx="1.5" cy="1.5" r="1" fill="currentColor"/><circle cx="4.5" cy="1.5" r="1" fill="currentColor"/><circle cx="1.5" cy="5" r="1" fill="currentColor"/><circle cx="4.5" cy="5" r="1" fill="currentColor"/><circle cx="1.5" cy="8.5" r="1" fill="currentColor"/><circle cx="4.5" cy="8.5" r="1" fill="currentColor"/></svg>',e}var ce=!1;function Xe(e,t){const s=document.createElement("div");s.className="emboss-sidebar-cell emboss-sidebar-task",s.dataset.id=e.id,s.style.height=`${t.scale.rowHeight}px`,s.style.paddingLeft=`${48+e.depth*16}px`;const n=document.createElement("span");if(n.className="emboss-sidebar-dot",ce){const p=ge(e,t.rows);p&&(n.style.background=p)}const a=document.createElement("span");a.className="emboss-sidebar-name",a.textContent=e.name;const o=document.createElement("span");o.className="emboss-sidebar-delete",o.textContent="×";const i=document.createElement("span");i.className="emboss-sidebar-add-child",i.textContent="+",i.dataset.addParent=e.id;const r=document.createElement("div");return r.className="emboss-sidebar-name-area",r.appendChild(a),e.assignee&&r.appendChild(Ge(e,ce,22)),r.appendChild(i),r.appendChild(o),s.prepend(Re()),s.appendChild(n),s.appendChild(r),s}function Ot(e,t){const s=t.collapsed[e.id],n=e.children?e.children.length:0,a=document.createElement("div");a.className="emboss-sidebar-cell emboss-sidebar-phase",a.dataset.id=e.id,a.style.height=`${Ce(e,t)}px`,a.style.paddingLeft=`${16+e.depth*16}px`;const o=document.createElement("span");o.className="emboss-sidebar-chevron",s&&o.classList.add("collapsed");const i=document.createElement("span");if(i.className="emboss-sidebar-pill",i.dataset.phaseId=e.id,ce){const c=ge(e,t.rows);c&&(i.style.backgroundColor=c)}const r=document.createElement("span");r.className="emboss-sidebar-name emboss-sidebar-phase-name",r.textContent=e.name;const p=document.createElement("span");p.className="emboss-sidebar-badge",p.textContent=String(n);const h=document.createElement("span");h.className="emboss-sidebar-delete",h.textContent="×";const f=document.createElement("span");f.className="emboss-sidebar-add-child",f.textContent="+",f.dataset.addParent=e.id;const b=document.createElement("div");return b.className="emboss-sidebar-name-area",b.appendChild(r),b.appendChild(p),b.appendChild(f),b.appendChild(h),a.prepend(Re()),a.appendChild(o),a.appendChild(i),a.appendChild(b),a}function Bt(e,t){const s=document.createElement("div");s.className="emboss-sidebar-cell emboss-sidebar-subtask",s.dataset.id=e.id,s.style.height=`${t.scale.rowHeight}px`,s.style.paddingLeft=`${48+e.depth*16}px`;const n=document.createElement("span");if(n.className="emboss-sidebar-dot emboss-sidebar-dot-sm",ce){const r=ge(e,t.rows);r&&(n.style.background=r)}const a=document.createElement("span");a.className="emboss-sidebar-name",a.textContent=e.name;const o=document.createElement("span");o.className="emboss-sidebar-delete",o.textContent="×";const i=document.createElement("div");return i.className="emboss-sidebar-name-area",i.appendChild(a),e.assignee&&i.appendChild(Ge(e,ce,22)),i.appendChild(o),s.prepend(Re()),s.appendChild(n),s.appendChild(i),s}function Yt(e,t){const s=document.createElement("div");s.className="emboss-sidebar-cell emboss-sidebar-milestone",s.dataset.id=e.id,s.style.height=`${t.scale.rowHeight}px`,s.style.paddingLeft=`${48+e.depth*16}px`;const n=document.createElement("span");if(n.className="emboss-sidebar-diamond",ce){const r=ge(e,t.rows);r&&(n.style.borderColor=r)}const a=document.createElement("span");a.className="emboss-sidebar-name emboss-sidebar-milestone-name",a.textContent=e.name;const o=document.createElement("span");o.className="emboss-sidebar-delete",o.textContent="×";const i=document.createElement("div");return i.className="emboss-sidebar-name-area",i.appendChild(a),i.appendChild(o),s.prepend(Re()),s.appendChild(n),s.appendChild(i),s}function jt(e,t){const s=document.createElement("div");s.className="emboss-sidebar-cell emboss-sidebar-rail-cell emboss-sidebar-phase",s.dataset.id=e.id,s.style.height=`${Ce(e,t)}px`;const n=document.createElement("span");if(n.className="emboss-rail-pill",n.textContent=e.name.charAt(0).toUpperCase(),ce){const a=ge(e,t.rows);a&&(n.style.background=a)}return s.appendChild(n),s}function Ut(e,t){const s=document.createElement("div");s.className="emboss-sidebar-cell emboss-sidebar-rail-cell emboss-sidebar-task",s.dataset.id=e.id,s.style.height=`${t.scale.rowHeight}px`;const n=document.createElement("span");if(n.className="emboss-sidebar-dot",ce){const a=ge(e,t.rows);a&&(n.style.background=a)}return s.appendChild(n),s}function Ft(e,t){const s=document.createElement("div");s.className="emboss-sidebar-cell emboss-sidebar-rail-cell emboss-sidebar-subtask",s.dataset.id=e.id,s.style.height=`${t.scale.rowHeight}px`;const n=document.createElement("span");if(n.className="emboss-sidebar-dot emboss-sidebar-dot-sm",ce){const a=ge(e,t.rows);a&&(n.style.background=a)}return s.appendChild(n),s}function Vt(e,t){const s=document.createElement("div");s.className="emboss-sidebar-cell emboss-sidebar-rail-cell emboss-sidebar-milestone",s.dataset.id=e.id,s.style.height=`${t.scale.rowHeight}px`;const n=document.createElement("span");if(n.className="emboss-sidebar-diamond",ce){const a=ge(e,t.rows);a&&(n.style.borderColor=a)}return s.appendChild(n),s}var Ye={task:Xe,phase:Ot,subtask:Bt,milestone:Yt},Gt={task:Ut,phase:jt,subtask:Ft,milestone:Vt},Xt={name:"sidebar",type:"paid",bundle:"organize",sidebarRenderer:Ye,init(e){let t=null,s=null,n=null,a=null,o=null,i=null,r=!1,p=!1,h=null,f=!1;function b(){n&&(n.remove(),n=null)}function c(){a&&(a.remove(),a=null,o=null)}function y(){const u=e.state.selected;if(u){const m=e.state.rows.find(x=>x.id===u);if(m?.type==="phase")return m;if(m?.parentId){const x=e.state.rows.find(w=>w.id===m.parentId);if(x?.type==="phase")return x}}return e.state.rows.find(m=>m.type==="phase")||null}function C(u){if(n){b();return}c(),n=document.createElement("div"),n.className="emboss-add-menu";const m=[{label:"Add Phase",type:"phase"},{label:"Add Task",type:"task"},{label:"Add Milestone",type:"milestone"}];for(const w of m){const T=document.createElement("div");T.className="emboss-add-menu-item",T.dataset.addType=w.type,T.textContent=w.label,n.appendChild(T)}const x=u.getBoundingClientRect();n.style.top=`${x.bottom+4}px`,n.style.left=`${x.right-160}px`,document.body.appendChild(n),requestAnimationFrame(()=>{document.addEventListener("mousedown",g)})}function g(u){const m=u.target;m.closest(".emboss-sidebar-add-btn")||n&&!n.contains(m)&&(b(),document.removeEventListener("mousedown",g))}function D(u){b(),document.removeEventListener("mousedown",g);const m=`new-${Date.now()}`,x=y();if(i=m,r=!0,u==="phase"){const w={id:m,type:"phase",name:"New Phase",depth:0,parentId:null,collapsed:!1,hidden:!1,start:0,duration:14,progress:0,status:"upcoming",dependencies:[],children:[]};e.addRow(w)}else if(u==="task"){const w={id:m,type:"task",name:"New Task",depth:x?1:0,parentId:x?.id||null,collapsed:!1,hidden:!1,start:x?x.start:0,duration:5,progress:0,status:"upcoming",dependencies:[]};x?.children&&x.children.push(m);const T=x?N(x.id):null;e.addRow(w,T||x?.id)}else{const w={id:m,type:"milestone",name:"New Milestone",depth:x?1:0,parentId:x?.id||null,collapsed:!1,hidden:!1,start:x?x.start+x.duration:0,duration:0,progress:0,status:"upcoming",dependencies:[]};x?.children&&x.children.push(m);const T=x?N(x.id):null;e.addRow(w,T||x?.id)}}function N(u){const m=e.state.rows,x=m.findIndex(E=>E.id===u);if(x===-1)return null;const w=new Set([u]);let T=null;for(let E=x+1;E<m.length;E++){const P=m[E].parentId;if(P&&w.has(P))w.add(m[E].id),T=m[E].id;else break}return T}function A(u){const m=e.state.rows.find(W=>W.id===u);if(!m)return;const x=`new-${Date.now()}`,w=m.type==="phase",T=w?"task":"subtask",E=m.depth+1;i=x,r=!0;const P={id:x,type:T,name:w?"New Task":"New Subtask",depth:E,parentId:m.id,collapsed:!1,hidden:!1,start:m.start,duration:5,progress:0,status:"upcoming",dependencies:[]};m.children||(m.children=[]),m.children.push(x),e.state.collapsed[u]&&e.toggleCollapse(u);const O=N(u);e.addRow(P,O||u)}function M(u){const m=e.state.rows.find(w=>w.id===u);if(!m)return;i===u&&(i=null,r=!1);const x=new Set([u]);if(m.children?.length)for(const w of m.children){x.add(w);const T=e.state.rows.find(E=>E.id===w);T?.children?.length&&T.children.forEach(E=>x.add(E))}e.state.rows=e.state.rows.filter(w=>!x.has(w.id));for(const w of e.state.rows)w.children&&(w.children=w.children.filter(T=>!x.has(T)));e.render()}function z(u,m){if(!(h?.classList.contains("emboss-vivid")??!1))return;if(a&&o===m){c();return}c(),b(),o=m;const w=e.state.rows.find(O=>O.id===m);if(!w)return;const T=(w.phaseColor||"").toLowerCase();a=document.createElement("div"),a.className="emboss-color-picker";const E=document.createElement("div");E.className="emboss-color-grid";for(const O of Ae){const W=document.createElement("div");W.className="emboss-color-swatch",W.dataset.color=O,W.style.background=O,O.toLowerCase()===T&&W.classList.add("active"),E.appendChild(W)}a.appendChild(E);const P=u.getBoundingClientRect();a.style.top=`${P.bottom+4}px`,a.style.left=`${P.left}px`,document.body.appendChild(a),requestAnimationFrame(()=>{document.addEventListener("mousedown",d)})}function d(u){const m=u.target;m.closest(".emboss-sidebar-pill")||a&&!a.contains(m)&&(c(),document.removeEventListener("mousedown",d))}function l(u,m){const x=e.state.rows.find(W=>W.id===m);if(!x)return;const w=r&&i===m;i=m;const T=x.name,E=document.createElement("input");E.className="emboss-sidebar-edit-input",E.value=x.name,E.style.fontSize=x.type==="phase"?"13px":"12.5px",E.style.fontWeight=x.type==="phase"?"600":"400",x.type==="phase"&&E.classList.add("emboss-sidebar-edit-phase"),u.textContent="",u.appendChild(E),E.focus(),E.select();let P=!1;function O(){if(P)return;P=!0;const W=E.value.trim();i=null,r=!1,E.parentElement&&E.remove(),W?W!==T?e.updateRow(m,{name:W}):e.render():w?M(m):e.render()}E.addEventListener("blur",()=>{requestAnimationFrame(()=>{E.parentElement&&O()})}),E.addEventListener("keydown",W=>{W.key==="Enter"&&(W.preventDefault(),O()),W.key==="Escape"&&(P=!0,i=null,r=!1,E.parentElement&&E.remove(),w?M(m):e.render())})}e.on("afterRender",(u,m,x)=>{if(h=u,ce=u.classList.contains("emboss-vivid"),u.classList.toggle("emboss-sidebar-collapsed",f),!t){let V=function(I,R){for(let L=I-1;L>=0;L--)if(R[L].type==="phase")return R[L];return null},Z=function(I,R){for(let L=I+1;L<R.length;L++)if(R[L].type==="phase")return L;return R.length},$=function(I,R,L){if(R===0)return null;if(R===1){for(let q=I-1;q>=0;q--)if(L[q].type==="phase")return L[q];return null}for(let q=I-1;q>=0;q--){if(L[q].type==="phase")return null;if(L[q].type==="task"&&L[q].depth===1)return L[q]}return null},j=function(I,R,L,q){const H=L.indexOf(I);if(R===H||R===H+1)return!1;const ie=q??I.depth;if(ie===0){if(I.type==="phase"){const we=Z(H,L);if(R>=H&&R<=we)return!1}const le=L[R];return!le||le.type==="phase"}return ie===1?V(R,L)!==null:ie===2?$(R,2,L)!==null:!1},k=function(I,R){const L=e.state.rows,q=L.filter(Y=>!Y.hidden),H=L.find(Y=>Y.id===I);if(!H)return;const ie=[H];if(H.type==="phase"&&H.children?.length)for(const Y of L)H.children.includes(Y.id)&&ie.push(Y);const le=q[R],we=le?L.indexOf(le):L.length,Se=new Set(ie.map(Y=>Y.id)),re=L.filter(Y=>!Se.has(Y.id));let ue=we;for(let Y=0;Y<we;Y++)Se.has(L[Y].id)&&ue--;if(re.splice(ue,0,...ie),e.state.rows=re,K!==null&&K!==H.depth){const Y=K===0?"phase":K===1?"task":"subtask",me=H.parentId&&re.find(Ee=>Ee.id===H.parentId)||null,be=$(re.indexOf(H),K,re);if(e.emit("onRowReparent",H,me,be,Y)===!1){e.render();return}me?.children&&(me.children=me.children.filter(Ee=>Ee!==H.id));const Te=H.children||[];if(H.depth=K,H.type=Y,H.parentId=be?.id||null,be&&(be.children||(be.children=[]),be.children.push(H.id)),Y==="phase"&&!H.children&&(H.children=[]),Y==="phase"&&Te.length){for(const Ee of Te){const De=re.find(Ke=>Ke.id===Ee);De&&(De.depth=1,De.type="task",De.parentId=H.id)}H.children=Te}}else if(H.type==="task"||H.type==="milestone"){const Y=V(re.indexOf(H),re);if(Y&&Y.id!==H.parentId){const me=re.find(be=>be.id===H.parentId);me?.children&&(me.children=me.children.filter(be=>be!==H.id)),H.parentId=Y.id,H.depth=1,Y.children||(Y.children=[]),Y.children.push(H.id)}}e.emit("onRowReorder",I,ue),e.render()},S=function(I){p=!0,ae=I;const R=e.state.rows.find(q=>q.id===I);if(R&&(K=R.depth),de=s.querySelector(`[data-id="${I}"]`),!de)return;const L=de.getBoundingClientRect();Ne=ne-L.top,se=de.cloneNode(!0),se.classList.add("emboss-drag-ghost"),se.style.position="fixed",se.style.width=`${L.width}px`,se.style.left=`${L.left}px`,se.style.top=`${L.top}px`,se.style.pointerEvents="none",se.style.zIndex="1000",document.body.appendChild(se),de.classList.add("emboss-sidebar-dragging"),Q=document.createElement("div"),Q.className="emboss-drop-indicator",Q.style.display="none",s.appendChild(Q),document.addEventListener("mousemove",U),document.addEventListener("mouseup",B),document.addEventListener("keydown",_)},U=function(I){if(!p||!s||!ae)return;se&&(se.style.top=`${I.clientY-Ne}px`);const R=s.getBoundingClientRect(),L=I.clientY-R.top+s.scrollTop,q=e.state.rows.filter(ue=>!ue.hidden);let H=0,ie=q.length;for(let ue=0;ue<q.length;ue++){const Y=Ce(q[ue],e.state);if(L<H+Y/2){ie=ue;break}H+=Y}ie=Math.max(0,Math.min(ie,q.length));const le=e.state.rows.find(ue=>ue.id===ae);if(!le)return;const we=I.clientX-pe,Se=Math.round(we/40);let re=Math.max(0,Math.min(2,le.depth+Se));if(le.type==="phase"&&le.children?.length&&(re=0),re>0&&!$(ie,re,q)&&(re=le.depth),K=re,j(le,ie,q,K)){fe=ie,Q.style.display="block";const ue=q.slice(0,ie).reduce((Y,me)=>Y+Ce(me,e.state),0);Q.style.top=`${ue}px`,Q.style.left=`${16+K*16}px`}else fe=null,Q.style.display="none"},B=function(){p&&fe!==null&&ae&&k(ae,fe),G()},_=function(I){I.key==="Escape"&&G()},G=function(){p=!1,de&&de.classList.remove("emboss-sidebar-dragging"),se&&se.remove(),se=null,Ne=0,pe=0,K=null,Q&&Q.remove(),Q=null,ae=null,de=null,fe=null,J=null,document.removeEventListener("mousemove",U),document.removeEventListener("mouseup",B),document.removeEventListener("keydown",_)};t=document.createElement("div"),t.className="emboss-sidebar-header",s=document.createElement("div"),s.className="emboss-sidebar";const ee=u.querySelector(".emboss-header"),F=u.querySelector(".emboss-body");u.insertBefore(t,ee),u.insertBefore(s,F),u.classList.add("emboss-has-sidebar"),F.addEventListener("scroll",()=>{s.scrollTop=F.scrollTop}),t.addEventListener("click",I=>{const R=I.target;if(R.closest(".emboss-sidebar-collapse")){f=!f,u.classList.toggle("emboss-sidebar-collapsed",f),e.render();return}R.closest(".emboss-sidebar-add-btn")&&C(R.closest(".emboss-sidebar-add-btn"))}),s.addEventListener("click",I=>{const R=I.target;if(R.closest(".emboss-sidebar-add-child")){I.stopPropagation();const H=R.closest(".emboss-sidebar-add-child");H.dataset.addParent&&A(H.dataset.addParent);return}if(R.closest(".emboss-sidebar-delete")){I.stopPropagation();const H=R.closest(".emboss-sidebar-cell");H?.dataset.id&&M(H.dataset.id);return}const L=R.closest(".emboss-sidebar-phase");if(L&&L.dataset.id){if(R.closest(".emboss-sidebar-chevron")){I.stopPropagation(),e.toggleCollapse(L.dataset.id);return}if(R.closest(".emboss-sidebar-name")){I.stopPropagation(),l(R.closest(".emboss-sidebar-name"),L.dataset.id);return}if(R.closest(".emboss-sidebar-pill")){I.stopPropagation(),z(R.closest(".emboss-sidebar-pill"),L.dataset.id);return}return}const q=R.closest(".emboss-sidebar-cell");q?.dataset.id&&R.closest(".emboss-sidebar-name")&&(I.stopPropagation(),l(R.closest(".emboss-sidebar-name"),q.dataset.id))}),document.body.addEventListener("click",I=>{const R=I.target,L=R.closest(".emboss-add-menu-item");L?.dataset.addType&&D(L.dataset.addType);const q=R.closest(".emboss-color-swatch");q?.dataset.color&&o&&(e.updateRow(o,{phaseColor:q.dataset.color}),c(),document.removeEventListener("mousedown",d))});let X=null,ne=0,pe=0,K=null,J=null,ae=null,de=null,Q=null,fe=null,se=null,Ne=0;s.addEventListener("mousedown",I=>{const L=I.target.closest(".emboss-sidebar-grip");if(!L)return;const q=L.closest(".emboss-sidebar-cell[data-id]");q?.dataset.id&&(J=q.dataset.id,ne=I.clientY,pe=I.clientX,X=window.setTimeout(()=>{X=null,J&&S(J)},150))}),s.addEventListener("mousemove",I=>{X&&J&&(Math.abs(I.clientY-ne)>5||Math.abs(I.clientX-pe)>5)&&(clearTimeout(X),X=null,S(J),J=null)}),s.addEventListener("mouseup",()=>{X&&(clearTimeout(X),X=null,J=null)})}t.innerHTML="";const w=document.createElement("div");w.className="emboss-sidebar-header-area";const T=document.createElement("span");T.className="emboss-sidebar-header-label",T.textContent="Tasks",w.appendChild(T);const E=document.createElement("button");E.className="emboss-sidebar-add-btn",E.textContent="+",w.appendChild(E);const P=document.createElement("button");if(P.className="emboss-sidebar-collapse",P.textContent=f?"▶":"◀",w.appendChild(P),t.appendChild(w),s.querySelector(".emboss-sidebar-edit-input")||p)return;const W=x.rows.filter(V=>!V.hidden),oe=f?Gt:Ye,te=document.createDocumentFragment();for(const V of W){const Z=oe[V.type],$=Z?Z(V,x):Xe(V,x);$&&te.appendChild($)}if(s.innerHTML="",s.appendChild(te),i){const V=s.querySelector(`[data-id="${i}"]`);if(V){const Z=V.querySelector(".emboss-sidebar-name");Z&&(l(Z,i),V.scrollIntoView({block:"nearest"}))}}const v=u.querySelector(".emboss-bars");if(v&&(v.querySelectorAll(".emboss-inline-phase").forEach(V=>V.remove()),f)){const V=x.density==="dense";let Z=0;W.forEach(($,j)=>{const k=Ce($,e.state);if($.type!=="phase"){Z+=k;return}const S=document.createElement("div");S.className="emboss-inline-phase",S.dataset.id=$.id,S.style.top=`${Z}px`,S.style.height=`${k}px`;const U=document.createElement("span");U.className="emboss-inline-chevron",U.textContent=x.collapsed[$.id]?"▶":"▼";const B=document.createElement("span");if(B.className="emboss-inline-phase-name",B.textContent=$.name,ce){const _=ge($,x.rows);_&&(B.style.color=_)}if(S.appendChild(U),S.appendChild(B),!V){const _=$.children?.length||0;if(_>0){const G=document.createElement("span");G.className="emboss-inline-phase-count",G.textContent=String(_),S.appendChild(G)}}U.addEventListener("click",_=>{_.stopPropagation(),e.toggleCollapse($.id)}),v.appendChild(S),Z+=k})}})},styles:`
/* Grid layout when sidebar is active */
.emboss.emboss-has-sidebar {
  display: grid;
  grid-template-columns: var(--emboss-sidebar-w, 280px) 1fr;
  grid-template-rows: auto 1fr;
}

/* Sidebar header */
.emboss-sidebar-header {
  grid-column: 1;
  grid-row: 1;
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 0 12px 0 16px;
  background: var(--emboss-surface);
  border-right: 1px solid var(--emboss-border);
  border-bottom: 1px solid var(--emboss-border);
}
.emboss-sidebar-header-area {
  flex: 1;
  display: flex;
  align-items: center;
  min-width: 0;
}
.emboss-sidebar-header-label {
  font-size: 12px;
  font-weight: 600;
  color: var(--emboss-ink-3);
  text-transform: uppercase;
  letter-spacing: 0.4px;
  flex: 1;
}

/* "+" button */
.emboss-sidebar-add-btn {
  width: 24px;
  height: 24px;
  border: none;
  border-radius: 4px;
  background: transparent;
  color: var(--emboss-ink-3);
  font-size: 18px;
  line-height: 1;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
}
.emboss-sidebar-add-btn:hover {
  background: var(--emboss-surface-2);
  color: var(--emboss-ink);
}

/* Add menu dropdown */
.emboss-add-menu {
  position: fixed;
  z-index: 50;
  min-width: 160px;
  background: var(--emboss-surface, #fff);
  border: 1px solid var(--emboss-border, #e5e7eb);
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.12);
  padding: 4px 0;
}
.emboss-add-menu-item {
  padding: 8px 14px;
  font-size: 13px;
  color: var(--emboss-ink, #1f2937);
  cursor: pointer;
}
.emboss-add-menu-item:hover {
  background: var(--emboss-surface-2, #f3f4f6);
}

/* Chart header — grid placement */
.emboss.emboss-has-sidebar .emboss-header {
  grid-column: 2;
  grid-row: 1;
  min-width: 0;
}

/* Sidebar body */
.emboss-sidebar {
  grid-column: 1;
  grid-row: 2;
  overflow-y: hidden;
  background: var(--emboss-surface);
  border-right: 1px solid var(--emboss-border);
}

/* Chart body — grid placement */
.emboss.emboss-has-sidebar .emboss-body {
  grid-column: 2;
  grid-row: 2;
  min-width: 0;
}

/* Hide redundant phase labels on timeline when sidebar shows them */
.emboss-has-sidebar .emboss-bar-label-phase {
  display: none;
}

/* ─── Cells ─────────────────────────────────────────────────────────────── */

.emboss-sidebar-cell {
  display: flex;
  align-items: center;
  gap: 8px;
  padding-right: 12px;
  border-bottom: 1px solid var(--emboss-border);
  white-space: nowrap;
  overflow: hidden;
  position: relative;
}
.emboss-sidebar-name-area {
  flex: 1;
  display: flex;
  align-items: center;
  gap: 8px;
  overflow: hidden;
  min-width: 0;
}

/* Status dot — grayscale default, vivid color set inline */
.emboss-sidebar-dot {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  flex-shrink: 0;
  background: var(--emboss-ink-3);
}
.emboss-sidebar-dot-sm {
  width: 6px;
  height: 6px;
  opacity: 0.7;
}

/* Name */
.emboss-sidebar-name {
  overflow: hidden;
  text-overflow: ellipsis;
  font-size: 12.5px;
  color: var(--emboss-ink);
  text-decoration: none;
  cursor: text;
  flex: 1;
}
.emboss-sidebar-task .emboss-sidebar-name {
  color: var(--emboss-ink-2);
}
.emboss-sidebar-subtask .emboss-sidebar-name {
  color: var(--emboss-ink-3);
}
.emboss-sidebar-milestone .emboss-sidebar-name {
  color: var(--emboss-ink-2);
}

/* ─── Delete button ─────────────────────────────────────────────────────── */

.emboss-sidebar-delete {
  width: 16px;
  height: 16px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  font-size: 14px;
  line-height: 1;
  color: var(--emboss-ink-4);
  cursor: pointer;
  border-radius: 3px;
  opacity: 0;
  transition: opacity 0.15s;
}
.emboss-sidebar-cell:hover .emboss-sidebar-delete {
  opacity: 1;
}
.emboss-sidebar-delete:hover {
  color: var(--emboss-ink-2);
  background: var(--emboss-surface-2);
}

/* ─── Grip handle ──────────────────────────────────────────────────────── */

.emboss-sidebar-grip {
  display: flex; align-items: center; justify-content: center;
  width: 12px; height: 20px; flex-shrink: 0;
  color: var(--emboss-ink-5); cursor: grab;
  opacity: 0; transition: opacity 0.15s;
  position: absolute; left: 4px; top: 50%; transform: translateY(-50%);
}
.emboss-sidebar-cell:hover .emboss-sidebar-grip { opacity: 1; }
.emboss-presentation .emboss-sidebar-grip { display: none; }

/* ─── Add child button ─────────────────────────────────────────────────── */

.emboss-sidebar-add-child {
  width: 16px; height: 16px; display: flex;
  align-items: center; justify-content: center; flex-shrink: 0;
  font-size: 14px; color: var(--emboss-ink-4); cursor: pointer;
  border-radius: 3px; opacity: 0; transition: opacity 0.15s;
}
.emboss-sidebar-cell:hover .emboss-sidebar-add-child { opacity: 1; }
.emboss-sidebar-add-child:hover { color: var(--emboss-ink-2); background: var(--emboss-surface-2); }
.emboss-presentation .emboss-sidebar-add-child { display: none; }

/* ─── Phase cell ────────────────────────────────────────────────────────── */

.emboss-sidebar-phase {
}
.emboss-sidebar-phase:hover {
  background: var(--emboss-surface-2);
}
.emboss-sidebar-phase-name {
  font-size: 13px;
  font-weight: 600;
}

/* Chevron */
.emboss-sidebar-chevron {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  width: 24px;
  height: 24px;
  flex-shrink: 0;
  color: var(--emboss-ink-4);
  cursor: pointer;
  transition: transform 0.15s;
  transform: rotate(90deg);
  border-radius: 4px;
}
.emboss-sidebar-chevron:hover {
  background: var(--emboss-surface-2);
}
.emboss-sidebar-chevron::before {
  content: '';
  display: block;
  width: 0;
  height: 0;
  border-left: 5px solid currentColor;
  border-top: 4px solid transparent;
  border-bottom: 4px solid transparent;
}
.emboss-sidebar-chevron.collapsed {
  transform: rotate(0deg);
}

/* Colored pill — 10px dot with 24px hit area via padding + background-clip */
.emboss-sidebar-pill {
  width: 24px;
  height: 24px;
  padding: 7px;
  background-clip: content-box;
  border-radius: 50%;
  flex-shrink: 0;
  background-color: var(--emboss-ink-3);
}
/* Clickable only in vivid mode */
.emboss-vivid .emboss-sidebar-pill {
  cursor: pointer;
}

/* Task count badge */
.emboss-sidebar-badge {
  font-size: 10px;
  font-weight: 500;
  color: var(--emboss-ink-3);
  background: var(--emboss-surface-2);
  border: 1px solid var(--emboss-border);
  padding: 1px 6px;
  border-radius: 8px;
  flex-shrink: 0;
}

/* ─── Milestone cell ────────────────────────────────────────────────────── */

.emboss-sidebar-diamond {
  width: 10px;
  height: 10px;
  transform: rotate(45deg);
  border: 1.5px solid var(--emboss-ink-4);
  border-radius: 1px;
  flex-shrink: 0;
}
.emboss-sidebar-milestone-name {
  font-style: italic;
}

/* ─── Inline edit input ─────────────────────────────────────────────────── */

.emboss-sidebar-edit-input {
  width: 100%;
  padding: 2px 6px;
  border: 1px solid var(--emboss-ink-4);
  border-radius: 6px;
  background: var(--emboss-surface, #fff);
  color: var(--emboss-ink, #1f2937);
  outline: none;
  font-family: inherit;
}
.emboss-sidebar-edit-input:focus {
  border-color: #6366f1;
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.12);
}
.emboss-sidebar-edit-phase {
  border-color: var(--emboss-ink-4) !important;
}

/* ─── Color picker ──────────────────────────────────────────────────────── */

.emboss-color-picker {
  position: fixed;
  z-index: 50;
  background: var(--emboss-surface, #fff);
  border: 1px solid var(--emboss-border, #e5e7eb);
  border-radius: 8px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.12);
  padding: 8px;
}
.emboss-color-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 6px;
}
.emboss-color-swatch {
  width: 24px;
  height: 24px;
  border-radius: 50%;
  cursor: pointer;
  border: 2px solid transparent;
}
.emboss-color-swatch:hover {
  transform: scale(1.15);
}
.emboss-color-swatch.active {
  border-color: var(--emboss-ink, #1f2937);
}

/* ─── Drag reorder ─────────────────────────────────────────────────────── */

.emboss-sidebar-cell.emboss-sidebar-dragging {
  pointer-events: none;
  cursor: grabbing;
}
.emboss-sidebar-cell.emboss-sidebar-dragging > * {
  visibility: hidden;
}
.emboss-sidebar-cell.emboss-sidebar-dragging::after {
  content: '';
  position: absolute;
  inset: 4px 8px;
  border: 1.5px dashed var(--emboss-ink-5);
  border-radius: 4px;
}
.emboss-drag-ghost {
  opacity: 0.85;
  background: var(--emboss-surface);
  border: 1.5px solid var(--emboss-ink-3);
  border-radius: 6px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.15);
  transform: scale(1.02);
  display: flex;
  align-items: center;
  cursor: grabbing;
}
.emboss-vivid .emboss-drag-ghost {
  border-color: #3b82f6;
}
.emboss-drop-indicator {
  position: absolute;
  left: 8px;
  right: 8px;
  height: 2px;
  background: var(--emboss-ink-3);
  pointer-events: none;
  z-index: 30;
  border-radius: 1px;
  transform: translateY(-50%);
}
.emboss-vivid .emboss-drop-indicator {
  background: #3b82f6;
}
.emboss-drop-indicator::before,
.emboss-drop-indicator::after {
  content: '';
  position: absolute;
  width: 6px;
  height: 6px;
  border-radius: 50%;
  background: var(--emboss-ink-3);
  top: -2px;
}
.emboss-vivid .emboss-drop-indicator::before,
.emboss-vivid .emboss-drop-indicator::after {
  background: #3b82f6;
}
.emboss-drop-indicator::before { left: -3px; }
.emboss-drop-indicator::after { right: -3px; }

/* ─── Dense mode sidebar ───────────────────────────────────────────────── */

.emboss-dense.emboss-has-sidebar {
  grid-template-columns: var(--emboss-sidebar-w, 220px) 1fr;
}
.emboss-dense .emboss-sidebar-phase-name { font-size: 12px; }
.emboss-dense .emboss-sidebar-name { font-size: 11px; }
.emboss-dense .emboss-avatar { width: 18px; height: 18px; }
.emboss-dense .emboss-avatar-initials { font-size: 8px; }
/* Zebra stripe the sidebar cells to match grid */
.emboss-dense .emboss-sidebar-cell:nth-child(even) {
  background: var(--emboss-surface-2);
}

/* ─── Presentation mode sidebar ────────────────────────────────────────── */

.emboss-presentation.emboss-has-sidebar {
  grid-template-columns: var(--emboss-sidebar-w, 320px) 1fr;
}
.emboss-presentation .emboss-sidebar-phase-name { font-size: 14px; font-weight: 700; }
.emboss-presentation .emboss-sidebar-name { font-size: 13px; }
.emboss-presentation .emboss-avatar { width: 26px; height: 26px; }
.emboss-presentation .emboss-avatar-initials { font-size: 11px; }
.emboss-presentation .emboss-sidebar-cell { padding-right: 16px; }
/* Hide delete buttons — view only */
.emboss-presentation .emboss-sidebar-delete { display: none; }
/* Hide add button */
.emboss-presentation .emboss-sidebar-add-btn { display: none; }

/* ─── Avatars ──────────────────────────────────────────────────────────── */

.emboss-avatar {
  position: relative;
  width: 22px;
  height: 22px;
  border-radius: 50%;
  overflow: hidden;
  flex-shrink: 0;
  background: var(--emboss-ink-3);
}
.emboss-avatar-sm {
  width: 18px;
  height: 18px;
}
.emboss-avatar-initials {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 10px;
  font-weight: 700;
  color: #ffffff;
  text-shadow: 0 1px 2px rgba(0,0,0,0.4), 0 0 4px rgba(0,0,0,0.2);
  letter-spacing: 0.5px;
}
.emboss-avatar-sm .emboss-avatar-initials {
  font-size: 8px;
}
/* Glass highlight on avatar */
.emboss-avatar::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 50%;
  border-radius: 50% 50% 0 0;
  background: linear-gradient(180deg, rgba(255,255,255,0.35) 0%, transparent 100%);
  pointer-events: none;
}

/* ─── Collapse button ──────────────────────────────────────────────────── */

.emboss-sidebar-collapse {
  width: 24px;
  height: 24px;
  border-radius: 6px;
  border: none;
  background: transparent;
  color: var(--emboss-ink-4);
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 12px;
  flex-shrink: 0;
  transition: background 0.15s;
}
.emboss-sidebar-collapse:hover {
  background: var(--emboss-surface-2);
  color: var(--emboss-ink-2);
}

/* ─── Sidebar rail mode (collapsed) ───────────────────────────────────── */

.emboss.emboss-has-sidebar {
  transition: grid-template-columns 0.2s ease;
}
.emboss-sidebar {
  transition: width 0.2s ease;
}

.emboss-sidebar-collapsed.emboss-has-sidebar {
  grid-template-columns: 48px 1fr;
}
.emboss-sidebar-collapsed .emboss-sidebar-header-label,
.emboss-sidebar-collapsed .emboss-sidebar-add-btn { display: none; }
.emboss-sidebar-collapsed .emboss-sidebar { width: 48px; }
.emboss-sidebar-collapsed .emboss-sidebar-header { padding: 0; justify-content: center; }
.emboss-sidebar-collapsed .emboss-sidebar-header-area { flex: none; }

/* Rail cells: center content, no padding */
.emboss-sidebar-rail-cell {
  justify-content: center;
  padding: 0 !important;
  gap: 0;
}

/* Rail phase pill — 30×30 rounded square with first letter */
.emboss-rail-pill {
  width: 30px;
  height: 30px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: #fff;
  font-weight: 600;
  font-size: 13px;
  background: var(--emboss-ink-3);
  flex-shrink: 0;
}

/* Rail milestone diamond: 8×8 */
.emboss-sidebar-rail-cell .emboss-sidebar-diamond {
  width: 8px;
  height: 8px;
}

/* Dense mode rail — 36px, smaller pills */
.emboss-sidebar-collapsed.emboss-dense.emboss-has-sidebar {
  grid-template-columns: 36px 1fr;
}
.emboss-sidebar-collapsed.emboss-dense .emboss-sidebar { width: 36px; }
.emboss-sidebar-collapsed.emboss-dense .emboss-rail-pill {
  width: 24px;
  height: 24px;
  font-size: 11px;
  border-radius: 6px;
}

/* Presentation mode rail — 48px */
.emboss-sidebar-collapsed.emboss-presentation.emboss-has-sidebar {
  grid-template-columns: 48px 1fr;
}
.emboss-sidebar-collapsed.emboss-presentation .emboss-sidebar { width: 48px; }

/* ─── Inline timeline phases (rail mode) ───────────────────────────────── */

.emboss-inline-phase {
  display: flex;
  align-items: center;
  gap: 6px;
  padding-left: 12px;
  position: absolute;
  left: 0;
  z-index: 5;
  pointer-events: auto;
}

.emboss-inline-chevron {
  font-size: 10px;
  color: var(--emboss-ink-4);
  cursor: pointer;
  width: 16px;
  text-align: center;
  user-select: none;
}
.emboss-inline-chevron:hover {
  color: var(--emboss-ink-2);
}

.emboss-inline-phase-name {
  font-size: 13px;
  font-weight: 600;
  color: var(--emboss-ink-2);
  white-space: nowrap;
  pointer-events: none;
}
.emboss-dark .emboss-inline-phase-name {
  color: var(--emboss-ink);
}

.emboss-inline-phase-count {
  font-size: 11px;
  color: var(--emboss-ink-4);
  background: var(--emboss-surface-2);
  padding: 1px 6px;
  border-radius: 8px;
  pointer-events: none;
}

/* Dense: smaller inline phase labels, no count badge */
.emboss-dense .emboss-inline-phase-name { font-size: 11px; }
.emboss-dense .emboss-inline-chevron { font-size: 8px; }
`},Kt={name:"phases",type:"paid",bundle:"organize",barRenderer:{},init(e){}},je=["#3b82f6","#8b5cf6","#ec4899","#f59e0b","#10b981","#ef4444","#06b6d4","#f97316"];function Qt(e,t,s,n){const a=s.density==="dense",o=e.start*t.dayWidth,i=Math.round(t.rowHeight/2),r=s.density==="presentation"?24:a?14:20,p=r/2,h=e.parentId?s.rows.find(g=>g.id===e.parentId&&g.type==="phase"):null,f=h?s.rows.filter(g=>g.type==="phase").indexOf(h):0,b=f>=0?f:0,c=h?.phaseColor||je[b%je.length],y=document.createElement("div");y.className="emboss-milestone",y.dataset.id=e.id,y.dataset.status=e.status,y.style.cssText=`left:${o-p}px;top:${i-p}px;width:${r}px;height:${r}px;`,y.style.setProperty("--phase-c",c);const C=document.createElement("div");if(C.className="emboss-milestone-diamond",e.progress>0){const g=document.createElement("div");g.className="emboss-milestone-fill",g.style.height=`${e.progress}%`,C.appendChild(g)}if(y.appendChild(C),a)(n?.classList.contains("emboss-has-sidebar")??!1)||(y.addEventListener("mouseenter",()=>{const D=document.createElement("div");D.className="emboss-dense-tag",D.textContent=e.name,D.style.left=`${o}px`,D.style.top=`${i-p-18}px`,y._denseTag=D,y.parentElement?.appendChild(D)}),y.addEventListener("mouseleave",()=>{y._denseTag?.remove(),y._denseTag=null}));else{const g=document.createElement("div");g.className="emboss-milestone-label",g.style.fontSize=`${t.labelSize}px`,g.textContent=e.name,y.appendChild(g)}return y}var Jt={name:"milestones",type:"paid",bundle:"organize",enrichRows(e){return e.map(t=>{if(t.type!=="milestone"||!t.dependencies.length)return t;const s=t.dependencies.map(r=>e.find(p=>p.id===r)).filter(Boolean);if(!s.length)return t;const n=Math.round(s.reduce((r,p)=>r+p.progress,0)/s.length),a=s.every(r=>r.status==="done"),o=s.some(r=>r.status==="active"||r.status==="done"),i=a?"done":o?"active":t.status;return{...t,progress:n,status:i}})},barRenderer:{milestone:Qt},styles:`
.emboss-milestone {
  position: absolute;
  cursor: pointer;
  user-select: none;
  outline: none;
}
.emboss-milestone-diamond {
  width: 100%;
  height: 100%;
  box-sizing: border-box;
  transform: rotate(45deg);
  border: 2.5px solid var(--emboss-ink-4);
  border-radius: 3px;
  background: var(--emboss-surface);
  overflow: hidden;
  transition: transform 0.15s;
}
.emboss-vivid .emboss-milestone-diamond {
  border-color: var(--phase-c);
}
.emboss-milestone:hover .emboss-milestone-diamond {
  transform: rotate(45deg) scale(1.2);
}
/* Glass highlight */
.emboss-milestone-diamond::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 50%;
  background: linear-gradient(180deg, rgba(255,255,255,0.4) 0%, transparent 100%);
  pointer-events: none;
}
/* Progress fill — anchored to bottom */
.emboss-milestone-fill {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  opacity: 0.6;
  background: var(--emboss-ink-4);
}
.emboss-vivid .emboss-milestone-fill {
  background: var(--phase-c);
}
/* Done state */
.emboss-milestone[data-status="done"] {
  opacity: var(--emboss-opacity-done);
}
/* Label — right of diamond */
.emboss-milestone-label {
  position: absolute;
  top: 50%;
  left: calc(100% + 8px);
  transform: translateY(-50%);
  white-space: nowrap;
  color: var(--emboss-ink-2);
  font-style: italic;
  pointer-events: none;
}
/* Dense: smaller diamond, no glass */
.emboss-dense .emboss-milestone-diamond {
  border-width: 2px;
}
.emboss-dense .emboss-milestone-diamond::before { display: none; }
/* Presentation: larger diamond */
.emboss-presentation .emboss-milestone-diamond {
  border-width: 3px;
}
`},Zt={name:"inline-edit",type:"paid",bundle:"organize",init(e){}};function $e(e,t){const s=new Date(e);return s.setDate(s.getDate()+t),s}function Ue(e,t){return Math.round((t.getTime()-e.getTime())/864e5)}function Fe(e){const t=e.getFullYear(),s=String(e.getMonth()+1).padStart(2,"0"),n=String(e.getDate()).padStart(2,"0");return`${t}-${s}-${n}`}function es(e,t,s){if(s)return`${e.getMonth()+1}/${e.getDate()} – ${t.getMonth()+1}/${t.getDate()}`;const n=e.toLocaleString("en",{month:"short"}),a=t.toLocaleString("en",{month:"short"}),o=e.getDate(),i=t.getDate(),r=e.getFullYear(),p=t.getFullYear();return r!==p?`${n} ${o} ‘${String(r).slice(2)} – ${a} ${i} ‘${String(p).slice(2)}`:n===a?`${n} ${o} – ${i}`:`${n} ${o} – ${a} ${i}`}function ts(e,t){return t?`${e.getMonth()+1}/${e.getDate()}`:`${e.toLocaleString("en",{month:"short"})} ${e.getDate()}`}var ss={duration:76,dates:120},ns={duration:"DURATION",dates:"DATES"},as={name:"columns",type:"paid",bundle:"columns",init(e){if(!(e.extensions?e.extensions.some(r=>r.name==="sidebar"):!1)){console.info("Emboss: Columns requires the Organize extension.");return}let s=e.options?.sidebar?.columns||[],n=null;function a(){n&&(n.remove(),n=null)}e.setSidebarColumns=r=>{s=r,e.render()},e.on("afterRender",(r,p,h)=>{if(s.length===0){r.style.removeProperty("--emboss-sidebar-w");return}const f=r.classList.contains("emboss-sidebar-collapsed"),b=h.density==="dense",c=h.density==="presentation",y=b?{duration:56,dates:96}:c?{duration:66,dates:100}:ss,C=s.reduce((M,z)=>M+(y[z]||0),0);if(f){r.style.removeProperty("--emboss-sidebar-w");return}const g=b?220:280;r.style.setProperty("--emboss-sidebar-w",`${g+C}px`);const D=r.querySelector(".emboss-sidebar-header");if(D){D.querySelectorAll(".emboss-sidebar-header-col").forEach(M=>M.remove());for(const M of s){const z=document.createElement("div");z.className=`emboss-sidebar-header-col emboss-sidebar-col-${M}`,z.textContent=ns[M]||M.toUpperCase(),D.appendChild(z)}}const N=r.querySelector(".emboss-sidebar");if(!N)return;N.querySelectorAll(".emboss-sidebar-cell").forEach(M=>{const z=M,d=z.dataset.id;if(!d)return;const l=h.rows.find(u=>u.id===d);if(l){z.querySelectorAll(".emboss-sidebar-col").forEach(u=>u.remove());for(const u of s){const m=document.createElement("div");if(m.className=`emboss-sidebar-col ${u}`,u==="duration")l.type==="milestone"?m.textContent="—":l.type==="phase"?m.textContent="":(m.textContent=`${l.duration}d`,m.classList.add("editable"),m.addEventListener("click",x=>{x.stopPropagation(),a(),o(m,l,e)}));else if(u==="dates")if(l.type==="phase")m.textContent="";else{const x=$e(p.startDate,l.start);if(l.type==="milestone"||l.duration===0)m.textContent=ts(x,b);else{const w=$e(p.startDate,l.start+l.duration);m.textContent=es(x,w,b)}m.classList.add("editable"),m.addEventListener("click",w=>{w.stopPropagation(),i(m,l,e,p.startDate)})}z.appendChild(m)}}})});function o(r,p,h){const f=p.duration,b=document.createElement("input");b.type="number",b.className="emboss-column-input",b.value=String(p.duration),b.min="1",b.style.width="40px",b.style.textAlign="right";let c=!1;function y(){if(c)return;c=!0;const C=parseInt(b.value),g=C&&C>0?C:f;b.parentElement&&b.remove(),h.updateRow(p.id,{duration:g})}b.addEventListener("blur",()=>{requestAnimationFrame(y)}),b.addEventListener("keydown",C=>{C.key==="Enter"&&(C.preventDefault(),b.blur()),C.key==="Escape"&&(c=!0,b.parentElement&&b.remove(),h.render())}),r.textContent="",r.appendChild(b),b.focus(),b.select()}function i(r,p,h,f){a();const b=$e(f,p.start),c=$e(f,p.start+p.duration);n=document.createElement("div"),n.className="emboss-date-popover";const y=document.createElement("input");y.type="date",y.className="emboss-date-input",y.value=Fe(b);const C=document.createElement("span");C.className="emboss-date-arrow",C.textContent="→";const g=document.createElement("input");g.type="date",g.className="emboss-date-input",g.value=Fe(c),p.type==="milestone"||p.duration===0?n.appendChild(y):(n.appendChild(y),n.appendChild(C),n.appendChild(g));const D=r.getBoundingClientRect();n.style.top=`${D.bottom+4}px`,n.style.left=`${D.left}px`,document.body.appendChild(n);function N(){const A=Ue(f,new Date(y.value));if(p.type==="milestone"||p.duration===0)h.updateRow(p.id,{start:A});else{const M=Ue(f,new Date(g.value)),z=Math.max(1,M-A);h.updateRow(p.id,{start:A,duration:z})}a()}y.addEventListener("change",N),g.addEventListener("change",N),requestAnimationFrame(()=>{document.addEventListener("mousedown",function A(M){const z=M.target;n&&!n.contains(z)&&z!==r&&(a(),document.removeEventListener("mousedown",A))})})}},styles:`
/* ─── Column headers ───────────────────────────────────────────────────── */

.emboss-sidebar-header-col {
  font-size: 10px;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.06em;
  color: var(--emboss-ink-4);
  padding: 8px 8px;
  border-left: 1px solid var(--emboss-border);
  text-align: center;
  flex-shrink: 0;
  white-space: nowrap;
  box-sizing: border-box;
}
.emboss-sidebar-col-duration { width: 76px; }
.emboss-sidebar-col-dates { width: 120px; text-align: left; }

/* ─── Column data cells ────────────────────────────────────────────────── */

.emboss-sidebar-col {
  padding: 0 8px;
  border-left: 1px solid var(--emboss-border);
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, monospace;
  font-size: 11.5px;
  color: var(--emboss-ink-3);
  white-space: nowrap;
  display: flex;
  align-items: center;
  height: 100%;
  flex-shrink: 0;
  box-sizing: border-box;
}
.emboss-sidebar-col.editable {
  cursor: pointer;
}
.emboss-sidebar-col.editable:hover {
  background: var(--emboss-surface-2);
}
.emboss-sidebar-col.duration {
  width: 76px;
  justify-content: flex-end;
}
.emboss-sidebar-col.dates {
  width: 120px;
  justify-content: flex-start;
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
  font-size: 11px;
}

/* ─── Duration edit input ──────────────────────────────────────────────── */

.emboss-column-input {
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, monospace;
  font-size: 11.5px;
  border: 1.5px solid var(--emboss-ink-4);
  border-radius: 4px;
  padding: 2px 4px;
  background: var(--emboss-surface);
  color: var(--emboss-ink);
  outline: none;
}
.emboss-column-input:focus {
  border-color: #6366f1;
  box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.15);
}

/* ─── Date picker popover ──────────────────────────────────────────────── */

.emboss-date-popover {
  position: fixed;
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 12px;
  background: var(--emboss-surface, #fff);
  border: 1px solid var(--emboss-border, #e5e7eb);
  border-radius: 10px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.12);
  z-index: 100;
}
.emboss-date-input {
  font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, monospace;
  font-size: 12px;
  border: 1px solid var(--emboss-border, #e5e7eb);
  border-radius: 6px;
  padding: 4px 8px;
  background: var(--emboss-surface, #fff);
  color: var(--emboss-ink, #1f2937);
}
.emboss-date-arrow {
  color: var(--emboss-ink-4);
  font-size: 14px;
}

/* ─── Dense mode column overrides ──────────────────────────────────────── */

.emboss-dense .emboss-sidebar-col-duration,
.emboss-dense .emboss-sidebar-col.duration { width: 56px; }
.emboss-dense .emboss-sidebar-col-dates,
.emboss-dense .emboss-sidebar-col.dates { width: 96px; }
.emboss-dense .emboss-sidebar-col { font-size: 10px; padding: 0 4px; }
.emboss-dense .emboss-sidebar-header-col { font-size: 9px; padding: 6px 4px; }

/* ─── Presentation mode column overrides ───────────────────────────────── */

.emboss-presentation .emboss-sidebar-col-duration,
.emboss-presentation .emboss-sidebar-col.duration { width: 66px; }
.emboss-presentation .emboss-sidebar-col-dates,
.emboss-presentation .emboss-sidebar-col.dates { width: 100px; }
.emboss-presentation .emboss-sidebar-header-col { padding: 8px 4px; }

/* ─── Rail mode: hide columns ──────────────────────────────────────────── */

.emboss-sidebar-collapsed .emboss-sidebar-col,
.emboss-sidebar-collapsed .emboss-sidebar-header-col {
  display: none;
}
`},he=window.leantime||(window.leantime={});he.embossAdapter=function(){function e(d){var l=new Date(d);return l.setHours(0,0,0,0),l}function t(d,l){return Math.round((l.getTime()-d.getTime())/864e5)}function s(d,l){var u=new Date(d);return u.setDate(u.getDate()+l),u}function n(d){var l=d.getFullYear(),u=String(d.getMonth()+1).padStart(2,"0"),m=String(d.getDate()).padStart(2,"0");return l+"-"+u+"-"+m+" 00:00:00"}var a="#00b893",o=["#8D99A6","#124F7D"];function i(d){return!d||d.trim()===""||d==="null"||d.indexOf("var(")===0||o.indexOf(d.toUpperCase())!==-1||o.indexOf(d)!==-1?a:d}var r={Day:"day",Week:"week",Month:"month",Quarter:"quarter"},p={day:"Day",week:"Week",month:"Month",quarter:"Quarter"};function h(d){for(var l=null,u=0;u<d.length;u++){var m=e(d[u].start);(!l||m<l)&&(l=m)}return l||(l=new Date),s(l,-7)}function f(d,l){var u=e(d.start),m=e(d.end),x=t(l,u),w=Math.max(7,t(u,m)),T=[];d.dependencies&&d.dependencies!==""&&(T=String(d.dependencies).split(",").map(function(te){return te.trim()}).filter(Boolean));var E=parseFloat(d.progress)||0,P="task";d.type==="milestone"?P="phase":d.type==="subtask"&&(P="subtask");var O=0,W=null;d.type!=="milestone"&&(d.milestoneid&&String(d.milestoneid)!=="0"&&String(d.milestoneid)!==""?(W=String(d.milestoneid),O=1):T.length>0&&(W=T[0],O=1));var oe="upcoming";return E>=100?oe="done":E>0&&(oe="active"),{id:String(d.id),type:P,name:d.name||"",start:x,duration:w,progress:E,depth:O,parentId:W,collapsed:!1,hidden:!1,status:oe,dependencies:T,phaseColor:i(d.bg_color),_ltId:d.id,_ltType:d.type,_ltProjectName:d.projectName||null,_ltSortIndex:d.sortIndex,_ltThumbnail:d.thumbnail||null}}function b(d,l){var u=e(d.end),m=t(l,u),x=parseFloat(d.progress)||0,w="upcoming";return x>=100?w="done":x>0&&(w="active"),{id:String(d.id)+"-end",type:"milestone",name:d.name||"",start:m,duration:0,progress:x,depth:1,parentId:String(d.id),collapsed:!1,hidden:!1,status:w,dependencies:[String(d.id)],_ltId:d.id,_ltType:d.type,_ltProjectName:d.projectName||null,_ltSortIndex:d.sortIndex,_ltThumbnail:d.thumbnail||null,_ltIsDiamond:!0}}function c(d){for(var l=h(d),u=[],m={},x=[],w={},T=[],E=0;E<d.length;E++){var P=d[E];if(P.type==="milestone"){var O=String(P.id);m[O]=P,x.push(O),w[O]||(w[O]=[])}}for(var E=0;E<d.length;E++){var P=d[E];if(P.type!=="milestone"){var W=null;if(P.milestoneid&&String(P.milestoneid)!=="0"&&String(P.milestoneid)!==""){var O=String(P.milestoneid);m[O]&&(W=O)}if(!W&&P.dependencies&&P.dependencies!=="")for(var oe=String(P.dependencies).split(","),te=0;te<oe.length;te++){var v=oe[te].trim();if(m[v]){W=v;break}}W?(w[W]||(w[W]=[]),w[W].push(P)):T.push(P)}}for(var V=0;V<x.length;V++){var O=x[V],Z=m[O];u.push(f(Z,l));for(var $=w[O]||[],j=0;j<$.length;j++)u.push(f($[j],l));u.push(b(Z,l))}for(var E=0;E<T.length;E++)u.push(f(T[E],l));for(var k={},E=0;E<u.length;E++)u[E].type==="phase"&&(k[u[E].id]=u[E]);for(var E=0;E<u.length;E++)u[E].parentId&&k[u[E].parentId]&&(k[u[E].parentId].children||(k[u[E].parentId].children=[]),k[u[E].parentId].children.push(u[E].id));return{rows:u,projectStart:l}}function y(d,l){return n(s(l,d))}function C(d,l,u,m){he.ticketsRepository.updateMilestoneDates(d,l,u,m)}function g(d,l,u,m){fetch(he.appUrl+"/api/projects",{method:"PATCH",body:new URLSearchParams({id:d,start:l,end:u,sortIndex:m}),credentials:"include",headers:{"Content-Type":"application/x-www-form-urlencoded","X-Requested-With":"XMLHttpRequest"}})}function D(d,l){for(var u=l||he.appUrl+"/api/tickets",m={action:"ganttSort",payload:{}},x=0;x<d.length;x++)m.payload[d[x].id]=x+1;var w={};w.action=m.action;for(var T in m.payload)m.payload.hasOwnProperty(T)&&(w["payload["+T+"]"]=m.payload[T]);fetch(u,{method:"POST",body:new URLSearchParams(w),credentials:"include",headers:{"Content-Type":"application/x-www-form-urlencoded","X-Requested-With":"XMLHttpRequest"}})}function N(d,l){var u=p[l]||l;he.usersRepository.updateUserViewSettings(d,u)}function A(d){if(d){var l=d._ltId||d.id,u=d._ltType||d.type,m=l,x=u,w=String(l).split("-");w.length>1&&(w[0]==="pgm"?(x="project",m=w[1]):w[0]==="ticket"&&(x="ticket",m=w[1])),x==="milestone"||u==="milestone"?he.modals.openByUrl("/tickets/editMilestone/"+m):x==="project"?he.modals.openByUrl("/projects/showProject/"+m):he.modals.openByUrl("/tickets/showTicket/"+m)}}function M(d,l,u,m,x){var w=document.querySelector(d+" .emboss-bars"),T=document.querySelector(d+" .emboss-body");if(!w)return;function E(){for(var $=w.querySelectorAll(".emboss-bar-phase"),j=l.state.rows,k=0;k<$.length;k++){var S=$[k],U=S.dataset.id,B=j.find(function(Q){return Q.id===U});if(!S.querySelector(".emboss-phase-handle")){var _=document.createElement("div");_.className="emboss-phase-handle emboss-phase-handle-left";var G=document.createElement("div");G.className="emboss-phase-handle emboss-phase-handle-right",S.appendChild(_),S.appendChild(G)}if(B){var ee=j.filter(function(Q){return Q.parentId===U&&Q.type!=="milestone"&&!Q._ltIsDiamond}),F=0;if(ee.length>0){for(var X=0,ne=0;ne<ee.length;ne++)X+=parseFloat(ee[ne].progress)||0;F=Math.round(X/ee.length)}else F=parseFloat(B.progress)||0;B.progress=F;var pe=U+"-end",K=j.find(function(Q){return Q.id===pe});K&&(K.progress=F,K.status=F>=100?"done":F>0?"active":"upcoming");var J=S.querySelector(".emboss-phase-fill");J||(J=document.createElement("div"),J.className="emboss-phase-fill",S.insertBefore(J,S.firstChild)),J.style.width=Math.min(100,Math.max(0,F))+"%";var ae=S.querySelector(".emboss-bar-label");if(ae&&B.name){var de=F>0&&F<100?" · "+F+"%":F>=100?" · Done":"";ae.textContent=B.name+de}}}}E(),l.on("onRender",E);function P($){var j=$.id+"-end",k=l.state.rows.find(function(S){return S.id===j});k&&(k.start=$.start+$.duration)}function O(){return l.state.scale.rowHeight||40}function W(){return l.state.rows.filter(function($){return!$.hidden})}function oe($){var j=O(),k=Math.floor($/j),S=W();return Math.max(0,Math.min(k,S.length-1))}var te=document.createElement("div");te.className="emboss-reorder-indicator",te.style.cssText="position:absolute;left:0;right:0;height:2px;background:var(--accent1,#004766);z-index:50;pointer-events:none;display:none;",w.appendChild(te);var v=null,V=5,Z=1.8;w.addEventListener("mousedown",function($){if($.button===0){var j=$.target.closest(".emboss-phase-handle"),k=$.target.closest(".emboss-bar[data-id]");if(k){var S=k.classList.contains("emboss-bar-phase"),U=k.dataset.id,B=l.state.rows.find(function(F){return F.id===U});if(B&&!B._ltIsDiamond){var _="pending";S&&j&&(j.classList.contains("emboss-phase-handle-left")?_="resize-left":j.classList.contains("emboss-phase-handle-right")&&(_="resize-right")),(S||_!=="pending")&&($.preventDefault(),$.stopPropagation());var G=W(),ee=G.indexOf(B);v={row:B,barEl:k,isPhase:S,intent:_,startX:$.clientX,startY:$.clientY,originalStart:B.start,originalDuration:B.duration,dayWidth:l.state.scale.dayWidth,rowHeight:O(),originalIndex:ee,_newStart:null,_newDuration:null,_newIndex:null,ghost:null,coreCancelled:!1}}}}}),document.addEventListener("mousemove",function($){if(v){var j=$.clientX-v.startX,k=$.clientY-v.startY,S=Math.abs(j),U=Math.abs(k);if(v.intent==="pending"){var B=Math.max(S,U);if(B<V)return;if(U>S*Z&&U>V){if(v.intent="reorder",!v.isPhase&&!v.coreCancelled){v.coreCancelled=!0,v.barEl.style.opacity="";var _=w.querySelector(".emboss-bar-ghost");_&&_.remove()}}else if(v.isPhase)v.intent="move-h";else{v=null;return}}if($.preventDefault(),v.intent==="reorder"){v.ghost||(v.ghost=v.barEl.cloneNode(!0),v.ghost.classList.add("emboss-bar-ghost"),v.ghost.style.pointerEvents="none",v.ghost.style.zIndex="100",w.appendChild(v.ghost),v.barEl.style.opacity="0.3");var G=v.originalIndex*v.rowHeight;v.ghost.style.top=G+k+"px";var ee=w.getBoundingClientRect(),F=$.clientY-ee.top+(T?T.scrollTop:0),X=oe(F);v._newIndex=X,te.style.top=X*v.rowHeight+"px",te.style.display="block";return}var ne=Math.round(j/v.dayWidth);if(v.intent==="move-h"){var pe=v.originalStart+ne;v.barEl.style.left=pe*v.dayWidth+"px",v._newStart=pe,v._newDuration=v.originalDuration}else if(v.intent==="resize-right"){var K=Math.max(1,v.originalDuration+ne);v.barEl.style.width=K*v.dayWidth+"px",v._newStart=v.originalStart,v._newDuration=K}else if(v.intent==="resize-left"){var J=v.originalStart+ne,ae=Math.max(1,v.originalDuration-ne);v.barEl.style.left=J*v.dayWidth+"px",v.barEl.style.width=ae*v.dayWidth+"px",v._newStart=J,v._newDuration=ae}}}),document.addEventListener("mouseup",function($){if(v){if(v.ghost&&v.ghost.remove(),v.barEl.style.opacity="",te.style.display="none",v.intent==="reorder"&&v._newIndex!==null&&v._newIndex!==v.originalIndex){var j=W(),k=v.row,S=l.state.rows,U=S.indexOf(k),B=j[v._newIndex],_=B?S.indexOf(B):S.length-1;S.splice(U,1),_>U&&_--,S.splice(_,0,k);for(var G=null,ee=_-1;ee>=0;ee--){if(S[ee].type==="phase"){G=S[ee];break}if(S[ee].depth===0)break}if(k.type!=="phase"){var F=k.parentId,X=G?G.id:null;if(F!==X){if(F){var ne=S.find(function(se){return se.id===F});ne&&ne.children&&(ne.children=ne.children.filter(function(se){return se!==k.id}))}k.parentId=X,k.depth=X?1:0,G&&(G.children||(G.children=[]),G.children.push(k.id))}}var pe=x||(m==="project"?he.appUrl+"/api/projects":null);D(S,pe),l.render(),v=null;return}if(v.isPhase&&(v.intent==="move-h"||v.intent==="resize-left"||v.intent==="resize-right")){var k=v.row,K=v._newStart!==null?v._newStart:k.start,J=v._newDuration!==null?v._newDuration:k.duration;if(K!==v.originalStart||J!==v.originalDuration){k.start=K,k.duration=J,P(k);var ae=y(K,u),de=y(K+J,u),Q=k._ltId||k.id,fe=String(Q).split("-");fe.length>1&&fe[0]==="pgm"?g(fe[1],ae,de,0):m==="project"?g(Q,ae,de,0):C(Q,ae,de,0),l.render()}}v=null}}),l.on("onDragEnd",function($){if($.parentId){var j=l.state.rows.find(function(k){return k.id===$.parentId&&k.type==="phase"});j&&P(j)}})}function z(d,l,u){u=u||{};var m=c(l),x=m.rows,w=m.projectStart,T=r[u.viewMode]||u.viewMode||"month",E=u.readonly||!1,P=u.viewSettingKey||"roadmap",O=u.entityType||"ticket",W=u.sortApiUrl||null,oe=[Ze,dt,it,Kt,Jt];E||(oe.push(Xt),oe.push(Zt),oe.push(as));var te=document.querySelector(d);te&&te.classList.add("emboss-vivid");var v=new Wt(d,x,{licenseKey:"EMB-OCPSA-20301231-4a736e3f",view:T,density:"working",startDate:w,extensions:oe,moveDependencies:!0});v.on("onDragEnd",function(k,S){if(E||k._ltIsDiamond)return!1;var U=S.start!==void 0?S.start:k.start,B=S.duration!==void 0?S.duration:k.duration,_=y(U,w),G=y(U+B,w),ee=k._ltId||k.id,F=x.indexOf(k)+1,X=String(ee).split("-");X.length>1&&X[0]==="pgm"?g(X[1],_,G,F):X.length>1&&X[0]==="ticket"?C(X[1],_,G,F):O==="project"?g(ee,_,G,F):C(ee,_,G,F)}),v.on("onClick",function(k,S){A(k)});var V=document.querySelector(d+" .emboss-bars");V&&V.addEventListener("click",function(k){var S=k.target.closest(".emboss-milestone[data-id], .emboss-bar-phase[data-id]");if(S){var U=S.dataset.id,B=v.state.rows.find(function(_){return _.id===U});B&&A(B)}}),E||M(d,v,w,O,W),v.on("onViewChange",function(k){N(P,k)}),v.on("onRowReorder",function(k,S){var U=W||(O==="project"?he.appUrl+"/api/projects":null);D(v.rows,U)});var Z=document.querySelector(d+" .emboss-sidebar"),$=document.querySelector(d+" .emboss-body");Z&&$&&Z.addEventListener("scroll",function(){$.scrollTop=Z.scrollTop});var j=document.querySelector("#ganttTimeControl");return j&&j.addEventListener("click",function(k){var S=k.target.closest("a");if(S){var U=S.getAttribute("data-value"),B=r[U]||U.toLowerCase();v.setView(B),j.querySelectorAll("a").forEach(function(_){_.classList.remove("active")}),S.classList.add("active"),document.querySelectorAll(".viewText").forEach(function(_){_.textContent=S.textContent.trim()})}}),v}return{init:z,convertTasks:c}}();
