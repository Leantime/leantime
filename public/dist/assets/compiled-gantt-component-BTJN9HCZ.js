function Qe(e,t){const s=t.getTime()-e.getTime();return Math.round(s/864e5)}function we(e,t){const s=new Date(e);return s.setDate(s.getDate()+t),s}function Ve(e){const t=e.getDay();return t===0||t===6}function Pe(e,t){return Math.round(e/t)}var Je=["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"];function Re(e,t=!1){return t?["January","February","March","April","May","June","July","August","September","October","November","December"][e]:Je[e]}var Ze={name:"today-marker",type:"free",init(e){let t=null;e.on("afterRender",(s,n,a)=>{const i=s.querySelector(".emboss-body");if(!i)return;const r=Qe(n.startDate,new Date),d=r*n.dayWidth;if(r<0||r>n.totalDays){t&&(t.remove(),t=null);return}t||(t=document.createElement("div"),t.className="emboss-today-col",t.innerHTML=`
          <div class="emboss-today-glow"></div>
          <div class="emboss-today-line"></div>
          <div class="emboss-today-ring"></div>
          <div class="emboss-today-dot"></div>
          <div class="emboss-today-label">Today</div>
        `),t.style.cssText=`left:${d-20}px;width:40px;height:${i.scrollHeight}px;`,(!t.parentElement||t.parentElement!==i)&&i.appendChild(t)})},styles:`
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
  `},We=["#3b82f6","#8b5cf6","#ec4899","#f59e0b","#10b981","#ef4444","#06b6d4","#f97316"];function et(e){let t=0;for(const s of e)t=(t<<5)-t+s.charCodeAt(0)|0;return We[Math.abs(t)%We.length]}function tt(e){const t=e.trim().split(/\s+/);return t.length===1?t[0][0].toUpperCase():(t[0][0]+t[t.length-1][0]).toUpperCase()}function st(e,t){const s=t?e.assigneeColor||et(e.assignee):"#9ca3af",n=tt(e.assignee);return`<div style="width:22px;height:22px;border-radius:50%;background:${s};position:relative;flex-shrink:0;overflow:hidden"><span style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:700;color:#ffffff;text-shadow:0 1px 2px rgba(0,0,0,0.4),0 0 4px rgba(0,0,0,0.2);letter-spacing:0.5px">${n}</span><span style="position:absolute;top:0;left:0;right:0;height:50%;border-radius:11px 11px 0 0;background:linear-gradient(180deg,rgba(255,255,255,0.35) 0%,transparent 100%);pointer-events:none"></span></div>`}function nt(e,t,s){const n=we(e,t),a=we(e,t+s-1),i={month:"short",day:"numeric"};return`${n.toLocaleDateString("en-US",i)} – ${a.toLocaleDateString("en-US",i)}`}function at(e){return e.charAt(0).toUpperCase()+e.slice(1)}function ot(e){return e==="active"?"var(--emboss-ink-3)":e==="done"?"var(--emboss-ink-4)":"var(--emboss-ink-5)"}var it={name:"tooltips",type:"free",init(e){let t=null,s=null,n=null,a=null,i=!1;function r(){return t||(t=document.createElement("div"),t.className="emboss-tip",document.body.appendChild(t)),t}function d(c,x,E){const v=r();v.classList.toggle("emboss-tip-dark",e.state.theme==="dark");const{scale:D}=e.state,N=c.duration>0?nt(D.startDate,c.start,c.duration):"",P=c.type!=="phase"?`<div class="emboss-tip-bar"><div class="emboss-tip-fill" style="width:${c.progress}%"></div></div>`:"";v.innerHTML=`
        ${c.phaseName?`<div class="emboss-tip-phase">${c.phaseName}</div>`:""}
        <div class="emboss-tip-name">${c.name}</div>
        <div class="emboss-tip-row">
          <span class="emboss-tip-dot" style="background:${ot(c.status)}"></span>
          <span>${at(c.status)}</span>
          ${c.progress>0&&c.progress<100?`<span class="emboss-tip-pct">${c.progress}%</span>`:""}
        </div>
        ${N?`<div class="emboss-tip-range">${N}</div>`:""}
        ${P}
        ${c.assignee?`<div class="emboss-tip-assignee">${st(c,i)}<span>${c.assignee}</span></div>`:""}
      `,p(v,x,E),v.classList.add("show")}function p(c,x,E){let v=x+8,D=E+16;const N=240,P=c.offsetHeight||80;v+N>window.innerWidth-8&&(v=x-N-8),D+P>window.innerHeight-8&&(D=E-P-8),c.style.left=`${v}px`,c.style.top=`${D}px`}function f(){t&&t.classList.remove("show"),a=null}e.on("onHover",c=>{n&&(clearTimeout(n),n=null),s&&(clearTimeout(s),s=null),c&&c.type!=="phase"&&e.state.density!=="presentation"?(a=c,s=setTimeout(()=>{a&&d(a,g,b)},200)):n=setTimeout(f,80)});let g=0,b=0;e.on("afterRender",c=>{i=c.classList.contains("emboss-vivid");const x=c.querySelector(".emboss-bars");!x||x.__embossTipWired||(x.__embossTipWired=!0,x.addEventListener("mousemove",E=>{g=E.clientX,b=E.clientY,t&&t.classList.contains("show")&&a&&p(t,g,b)}))})},styles:`
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
  `};function rt(e,t){return e.status==="done"&&t.status==="done"?{stroke:"var(--emboss-ink-5)",width:1.2,dash:"",opacity:.4}:t.status==="upcoming"?{stroke:"var(--emboss-ink-4)",width:1.5,dash:"5 3",opacity:.5}:{stroke:"var(--emboss-ink-3)",width:1.5,dash:"",opacity:.65}}var _e="http://www.w3.org/2000/svg",dt={name:"dependency-arrows",type:"free",init(e){let t=null;e.on("afterRender",(s,n,a)=>{const i=s.querySelector(".emboss-body");if(!i)return;const r=a.rows.filter(g=>!g.hidden),d=new Map;r.forEach((g,b)=>d.set(g.id,b)),t||(t=document.createElementNS(_e,"svg"),t.classList.add("emboss-dep")),t.setAttribute("width",String(n.totalDays*n.dayWidth)),t.setAttribute("height",String(r.length*n.rowHeight)),t.innerHTML="";const p=12,f=4;for(const g of r){if(!g.dependencies||g.dependencies.length===0)continue;const b=d.get(g.id);if(b!==void 0)for(const c of g.dependencies){const x=d.get(c);if(x===void 0)continue;const E=r[x],v=E.start*n.dayWidth,D=x*n.rowHeight+n.rowHeight/2,N=g.start*n.dayWidth,P=b*n.rowHeight+n.rowHeight/2,M=Math.min(v,N)-p,z=rt(E,g),Y=ut(v,D,N,P,M,f),o=document.createElementNS(_e,"path");o.setAttribute("d",Y),o.setAttribute("fill","none"),o.setAttribute("stroke",z.stroke),o.setAttribute("stroke-width",String(z.width)),o.setAttribute("stroke-linecap","round"),o.setAttribute("stroke-linejoin","round"),z.dash&&o.setAttribute("stroke-dasharray",z.dash),o.dataset.f=E.id,o.dataset.t=g.id,o.dataset.depOpacity=String(z.opacity),o.style.opacity="0",t.appendChild(o)}}(!t.parentElement||t.parentElement!==i)&&i.appendChild(t),qe(t,a.hoveredRow)}),e.on("onHover",s=>{t&&qe(t,e.state.hoveredRow)})},styles:`
    svg.emboss-dep { position: absolute; top: 0; left: 0; pointer-events: none; z-index: 15; overflow: visible; }
    svg.emboss-dep path { transition: opacity 0.2s; }
  `};function qe(e,t){const s=e.querySelectorAll("path");for(const n of s){const a=n.dataset;t&&(a.f===t||a.t===t)?n.style.opacity=a.depOpacity||"0.65":n.style.opacity="0"}}function ut(e,t,s,n,a,i){if(Math.abs(t-n)<1)return`M ${e} ${t} H ${a} H ${s}`;const r=n>t,d=Math.abs(e-a),p=Math.abs(n-t),f=Math.min(i,d,p/2),g=r?1:-1;return[`M ${e} ${t}`,`H ${a+f}`,`Q ${a} ${t} ${a} ${t+g*f}`,`V ${n-g*f}`,`Q ${a} ${n} ${a+f} ${n}`,`H ${s}`].join(" ")}var Oe={working:{day:{dayWidth:44,rowHeight:44,barHeight:26,barRadius:13,labelSize:11.5},week:{dayWidth:32,rowHeight:44,barHeight:26,barRadius:13,labelSize:11.5},month:{dayWidth:12,rowHeight:40,barHeight:22,barRadius:11,labelSize:11.5},quarter:{dayWidth:7,rowHeight:40,barHeight:22,barRadius:11,labelSize:11.5}},presentation:{day:{dayWidth:48,rowHeight:60,barHeight:34,barRadius:17,labelSize:13},week:{dayWidth:36,rowHeight:60,barHeight:34,barRadius:17,labelSize:13},month:{dayWidth:14,rowHeight:56,barHeight:30,barRadius:15,labelSize:12.5},quarter:{dayWidth:8,rowHeight:56,barHeight:30,barRadius:15,labelSize:12.5}},dense:{day:{dayWidth:44,rowHeight:30,barHeight:18,barRadius:9,labelSize:10},week:{dayWidth:32,rowHeight:30,barHeight:18,barRadius:9,labelSize:10},month:{dayWidth:10,rowHeight:28,barHeight:16,barRadius:8,labelSize:9.5},quarter:{dayWidth:5,rowHeight:28,barHeight:16,barRadius:8,labelSize:9.5}}},lt={day:30,week:42,month:90,quarter:180};function xe(e,t,s,n){const a=Oe[t]?.[e]??Oe.working.week,i=s.reduce((d,p)=>Math.max(d,p.start+p.duration),0),r=lt[e]??30;return{...a,totalDays:Math.max(i+14,r),startDate:n}}function ct(e,t){return{rows:e,view:"week",density:"working",theme:"grayscale",collapsed:{},selected:null,hoveredRow:null,moveDependencies:!1,settings:{markWeekends:!1,excludeWeekends:!1,holidays:[],ignoredDays:[]},scale:xe("week","working",e,t)}}function ze(e){const{rows:t,collapsed:s}=e;for(const n of t){if(!n.parentId){n.hidden=!1;continue}let a=n.parentId,i=!1;for(;a;){if(s[a]){i=!0;break}a=t.find(d=>d.id===a)?.parentId??null}n.hidden=i}}function pt(e,t,s,n){return e.type==="phase"?gt(e,t,s,n):mt(e,t,s,n)}function mt(e,t,s,n){const a=n?.classList.contains("emboss-vivid")??!1,i=s.density==="dense",r=s.density==="presentation",d=e.start*t.dayWidth,p=i?t.dayWidth<=5?6:t.dayWidth<=10?8:t.barHeight:t.barHeight,f=Math.max(e.duration*t.dayWidth,p),g=Math.round((t.rowHeight-t.barHeight)/2),b=t.barRadius,c=document.createElement("div");c.className="emboss-bar",c.dataset.id=e.id,c.dataset.status=e.status,c.dataset.type=e.type;const x=e.status==="done"?"opacity:var(--emboss-opacity-done,0.45);":"";c.style.cssText=`left:${d}px;width:${f}px;top:${g}px;height:${t.barHeight}px;border-radius:${b}px;${x}`;const E=document.createElement("div");E.className="emboss-bar-track",c.appendChild(E);const v=Math.max(0,Math.min(100,e.progress)),D=e.status==="upcoming"&&v===0,N=D?f:Math.max(v>0?14:0,f*v/100),P=v>=100||D?`${b}px`:`${b}px 0 0 ${b}px`,M=document.createElement("div");M.className="emboss-bar-fill",M.style.cssText=`width:${N}px;border-radius:${P};`;const z=a?ft(e,s):null;if(z&&(M.style.backgroundImage=ht(z.color,e.status),e.type==="subtask"&&(M.style.opacity="0.7")),c.appendChild(M),!i&&v>0&&v<100){const o=document.createElement("div");o.className="emboss-bar-marker",o.style.left=`${N-6}px`,c.appendChild(o)}const Y=document.createElement("div");if(Y.className="emboss-bar-label",Y.style.cssText=`font-size:${t.labelSize}px;height:${t.barHeight}px;line-height:${t.barHeight}px;`,i)n?.classList.contains("emboss-has-sidebar")??!1?f>50?(Y.textContent=e.name,Y.classList.add("emboss-bar-label-inside")):Y.style.display="none":(Y.style.display="none",c.addEventListener("mouseenter",()=>{const l=document.createElement("div");l.className="emboss-dense-tag",l.textContent=e.name,l.style.left=`${d}px`,l.style.top=`${g-18}px`,c._denseTag=l,c.parentElement?.appendChild(l)}),c.addEventListener("mouseleave",()=>{c._denseTag?.remove(),c._denseTag=null}));else if(r){const o=v>0&&v<100?` · ${v}%`:"";Y.textContent=e.name+o,f>50?(Y.classList.add("emboss-bar-label-inside"),Y.style.fontWeight="600",D&&Y.classList.add("emboss-bar-label-upcoming")):Y.classList.add("emboss-bar-label-outside")}else{const o=v>0&&v<100?` ${v}%`:"";Y.textContent=e.name+o,f<=70?Y.classList.add("emboss-bar-label-outside"):(Y.classList.add("emboss-bar-label-inside"),D&&Y.classList.add("emboss-bar-label-upcoming"))}if(c.appendChild(Y),i&&v>0&&v<100&&t.dayWidth>=8){const o=document.createElement("div");o.className="emboss-minibar",o.style.width=`${f*v/100}px`,z&&(o.style.background=z.color),c.appendChild(o)}if(s.density==="working"){const o=document.createElement("div");o.className="emboss-bar-handle emboss-bar-handle-left",c.appendChild(o);const l=document.createElement("div");l.className="emboss-bar-handle emboss-bar-handle-right",c.appendChild(l)}return c}var Me=["#3b82f6","#8b5cf6","#ec4899","#f59e0b","#10b981","#ef4444","#06b6d4","#f97316"];function bt(e){const t=parseInt(e.replace("#",""),16),s=(t>>16)/255,n=(t>>8&255)/255,a=(t&255)/255,i=Math.max(s,n,a),r=Math.min(s,n,a),d=(i+r)/2;if(i===r)return{h:0,s:0,l:d*100};const p=i-r,f=d>.5?p/(2-i-r):p/(i+r);let g=0;return i===s?g=((n-a)/p+(n<a?6:0))/6:i===n?g=((a-s)/p+2)/6:g=((s-n)/p+4)/6,{h:g*360,s:f*100,l:d*100}}function Ce(e,t,s){t/=100,s/=100;const n=t*Math.min(s,1-s),a=i=>{const r=(i+e/30)%12,d=s-n*Math.max(Math.min(r-3,9-r,1),-1);return Math.round(255*d).toString(16).padStart(2,"0")};return`#${a(0)}${a(8)}${a(4)}`}function ht(e,t){const s=bt(e),n=(s.h+50)%360;if(t==="done"){const i=Ce(s.h,s.s*.6,Math.min(s.l+15,80)),r=Ce(n,s.s*.6,Math.min(s.l+15,80));return`linear-gradient(90deg, ${i}, ${r})`}if(t==="upcoming"){const i=Ce(s.h,s.s*.8,Math.min(s.l+5,70)),r=Ce(n,s.s*.8,Math.min(s.l+5,70));return`linear-gradient(90deg, ${i}, ${r})`}const a=Ce(n,Math.min(s.s,85),Math.min(s.l+10,65));return`linear-gradient(90deg, ${e}, ${a})`}function ft(e,t){let s;if(e.type==="phase")s=e;else if(e.parentId){const i=t.rows.find(r=>r.id===e.parentId);i?.type==="phase"?s=i:i?.parentId&&(s=t.rows.find(r=>r.id===i.parentId&&r.type==="phase"))}if(!s)return null;const n=t.rows.filter(i=>i.type==="phase").indexOf(s),a=n>=0?n:0;return{color:s.phaseColor||Me[a%Me.length],idx:a}}function gt(e,t,s,n){const a=e.start*t.dayWidth,i=Math.max(e.duration*t.dayWidth,20),r=s.density==="presentation",d=r?7:5,p=r?4:3,f=Math.round((t.rowHeight-d)/2),g=n?.classList.contains("emboss-vivid")??!1,b=document.createElement("div");if(b.className="emboss-bar emboss-bar-phase",b.dataset.id=e.id,b.dataset.type="phase",b.style.cssText=`left:${a}px;width:${i}px;top:${f}px;height:${d}px;border-radius:${p}px;`,g){const x=s.rows.filter(D=>D.type==="phase").findIndex(D=>D.id===e.id),E=x>=0?x:0,v=e.phaseColor||Me[E%Me.length];b.style.background=v,b.style.opacity="0.25"}const c=document.createElement("div");return c.className="emboss-bar-label emboss-bar-label-phase",c.style.fontSize=`${t.labelSize}px`,c.textContent=e.name,b.appendChild(c),b}var vt=`
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
`;function yt(e,t){switch(t.view){case"day":return xt(e);case"week":return wt(e);case"month":return Et(e,t);case"quarter":return kt(e,t)}}function xt(e,t){const s=document.createElement("div");s.className="emboss-header-inner";const n=document.createElement("div");n.className="emboss-header-row emboss-header-row-top";const a=document.createElement("div");a.className="emboss-header-row emboss-header-row-bottom";let i=-1,r=null,d=0;for(let p=0;p<e.totalDays;p++){const f=we(e.startDate,p),g=f.getMonth(),b=f.getFullYear();g!==i&&(r&&(r.style.width=`${d*e.dayWidth}px`),r=document.createElement("span"),r.className="emboss-header-cell emboss-header-month",r.textContent=`${Re(g)} ${b}`,n.appendChild(r),i=g,d=0),d++;const c=document.createElement("span");c.className="emboss-header-cell emboss-header-day",Ve(f)&&c.classList.add("emboss-header-weekend"),c.style.width=`${e.dayWidth}px`,c.textContent=`${f.getDate()}`,a.appendChild(c)}return r&&(r.style.width=`${d*e.dayWidth}px`),s.appendChild(n),s.appendChild(a),s}function wt(e,t){const s=document.createElement("div");s.className="emboss-header-inner";const n=document.createElement("div");n.className="emboss-header-row emboss-header-row-top";const a=document.createElement("div");a.className="emboss-header-row emboss-header-row-bottom";let i=-1,r=null,d=0;for(let p=0;p<e.totalDays;p++){const f=we(e.startDate,p),g=f.getMonth(),b=f.getFullYear();if(g!==i&&(r&&(r.style.width=`${d*e.dayWidth}px`),r=document.createElement("span"),r.className="emboss-header-cell emboss-header-month",r.textContent=`${Re(g)} ${b}`,n.appendChild(r),i=g,d=0),d++,f.getDay()===1){const c=document.createElement("span");c.className="emboss-header-cell emboss-header-week",c.style.width=`${7*e.dayWidth}px`;const x={month:"short",day:"numeric"};c.textContent=f.toLocaleDateString("en-US",x),a.appendChild(c)}}return r&&(r.style.width=`${d*e.dayWidth}px`),s.appendChild(n),s.appendChild(a),s}function Et(e,t){const s=document.createElement("div");s.className="emboss-header-inner";const n=document.createElement("div");n.className="emboss-header-row";let a=-1,i=null,r=0;for(let d=0;d<e.totalDays;d++){const f=we(e.startDate,d).getMonth();f!==a&&(i&&(i.style.width=`${r*e.dayWidth}px`),i=document.createElement("span"),i.className="emboss-header-cell emboss-header-month",i.textContent=Re(f,t.density==="presentation"),n.appendChild(i),a=f,r=0),r++}return i&&(i.style.width=`${r*e.dayWidth}px`),s.appendChild(n),s}function kt(e,t){const s=document.createElement("div");s.className="emboss-header-inner";const n=t.density==="dense",a=document.createElement("div");a.className="emboss-header-row emboss-header-row-top";const i=n?null:document.createElement("div");i&&(i.className="emboss-header-row emboss-header-row-bottom");let r=-1,d=-1,p=null,f=null,g=0,b=0;for(let c=0;c<e.totalDays;c++){const x=we(e.startDate,c),E=x.getMonth(),v=x.getFullYear(),D=Math.floor(E/3);(D!==r||c===0)&&(p&&(p.style.width=`${g*e.dayWidth}px`),p=document.createElement("span"),p.className="emboss-header-cell emboss-header-quarter",p.textContent=`Q${D+1} ${v}`,a.appendChild(p),r=D,g=0),g++,i&&E!==d&&(f&&(f.style.width=`${b*e.dayWidth}px`),f=document.createElement("span"),f.className="emboss-header-cell emboss-header-month",f.textContent=Re(E),i.appendChild(f),d=E,b=0),i&&b++}return p&&(p.style.width=`${g*e.dayWidth}px`),f&&i&&(f.style.width=`${b*e.dayWidth}px`),s.appendChild(a),i&&s.appendChild(i),n&&a.classList.remove("emboss-header-row-top"),s}var Ct=`
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
`;function St(e,t,s,n){const a=document.createElement("div");a.className="emboss-grid-inner";const i=e.totalDays*e.dayWidth,r=n?n.reduce((f,g)=>f+g,0):s*e.rowHeight;for(let f=0;f<e.totalDays;f++){const g=we(e.startDate,f),b=f*e.dayWidth;if(Ve(g)&&t.settings.markWeekends){const v=document.createElement("div");v.className="emboss-grid-weekend",v.style.cssText=`left:${b}px;width:${e.dayWidth}px;height:${r}px;`,a.appendChild(v)}let x=!1,E=!1;if(t.view==="day"?x=!0:t.view==="week"?x=g.getDay()===1:x=g.getDate()===1,t.settings.markWeekends&&g.getDay()===6&&f>0&&(E=!0),(x||E)&&f>0){const v=document.createElement("div");v.className="emboss-grid-vline",E&&!x&&v.classList.add("emboss-grid-vline-boundary"),v.style.cssText=`left:${b}px;height:${r}px;`,a.appendChild(v)}}const d=t.density==="dense";let p=0;for(let f=0;f<=s;f++){const g=n?n[f]??e.rowHeight:e.rowHeight;if(d&&f<s&&f%2===1){const b=document.createElement("div");b.className="emboss-grid-stripe",b.style.cssText=`top:${p}px;width:${i}px;height:${g}px;`,a.appendChild(b)}if(f>0){const b=document.createElement("div");b.className="emboss-grid-hline",b.style.cssText=`top:${p}px;width:${i}px;`,a.appendChild(b)}p+=g}return a}var Dt=`
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
`;function $t(e,t){const s={};for(const i of t)for(const r of i.dependencies)s[r]||(s[r]=[]),s[r].push(i.id);const n=new Set,a=[e];for(;a.length;){const i=a.shift(),r=s[i]||[];for(const d of r)n.has(d)||(n.add(d),a.push(d))}return t.filter(i=>n.has(i.id))}function Lt(e,t){let s=null;function n(r){if(r.button!==0||t.getState().density==="presentation")return;const d=r.target;let p;d.classList.contains("emboss-bar-handle-left")?p="resize-left":d.classList.contains("emboss-bar-handle-right")?p="resize-right":d.classList.contains("emboss-bar-marker")?p="progress":p="move";const f=d.closest(".emboss-bar[data-id]");if(!f)return;const g=f.dataset.id,b=t.getState(),c=b.rows.find(D=>D.id===g);if(!c||c.type==="phase"||t.emit("onDragStart",c,p)===!1)return;const E=f.cloneNode(!0);E.classList.add("emboss-bar-ghost"),f.style.opacity="0.35",f.parentElement.appendChild(E);const v=[];if(p==="move"&&b.moveDependencies){const D=$t(g,b.rows);for(const N of D){if(N.type==="phase")continue;const P=e.querySelector(`.emboss-bar[data-id="${N.id}"]`);if(!P)continue;const M=P.cloneNode(!0);M.classList.add("emboss-bar-ghost"),P.style.opacity="0.35",P.parentElement.appendChild(M),v.push({row:N,ghost:M,barEl:P,originalStart:N.start})}}s={row:c,type:p,startX:r.clientX,startY:r.clientY,originalStart:c.start,originalDuration:c.duration,originalProgress:c.progress,ghost:E,barEl:f,depGhosts:v},r.preventDefault(),document.addEventListener("mousemove",a),document.addEventListener("mouseup",i)}function a(r){if(!s)return;const{row:d,type:p,startX:f,ghost:g,originalStart:b,originalDuration:c,originalProgress:x}=s,v=t.getState().scale.dayWidth,D=r.clientX-f,N=Pe(D,v);if(p==="move"){const P=(b+N)*v;g.style.left=`${Math.max(0,P)}px`;for(const M of s.depGhosts){const z=(M.originalStart+N)*v;M.ghost.style.left=`${Math.max(0,z)}px`}t.emit("onDragMove",d,{days:N})}else if(p==="resize-right"){const P=Math.max(1,c+N);g.style.width=`${P*v}px`,t.emit("onDragMove",d,{days:N})}else if(p==="resize-left"){const P=b+N,M=c-N;M>=1&&(g.style.left=`${P*v}px`,g.style.width=`${M*v}px`),t.emit("onDragMove",d,{days:N})}else if(p==="progress"){const P=c*v,M=Math.max(0,Math.min(100,Math.round(D/P*100+x))),z=g.querySelector(".emboss-bar-marker"),Y=g.querySelector(".emboss-bar-fill");Y&&(Y.style.width=`${Math.max(14,P*M/100)}px`),z&&(z.style.left=`${Math.max(14,P*M/100)-6}px`),t.emit("onDragMove",d,{days:0,progress:M})}}function i(r){if(!s)return;const{row:d,type:p,startX:f,originalStart:g,originalDuration:b,originalProgress:c,ghost:x,barEl:E,depGhosts:v}=s,N=t.getState().scale.dayWidth,P=r.clientX-f,M=Pe(P,N),z={};if(p==="move")z.start=Math.max(0,g+M);else if(p==="resize-right")z.duration=Math.max(1,b+M);else if(p==="resize-left"){const o=b-M;o>=1&&(z.start=g+M,z.duration=o)}else if(p==="progress"){const o=b*N;z.progress=Math.max(0,Math.min(100,Math.round(P/o*100+c)))}if(t.emit("onDragEnd",d,z)!==!1&&Object.keys(z).length>0&&(t.updateRow(d.id,z),p==="move"))for(const o of v)t.updateRow(o.row.id,{start:Math.max(0,o.originalStart+M)});x.remove(),E.style.opacity="";for(const o of v)o.ghost.remove(),o.barEl.style.opacity="";s=null,document.removeEventListener("mousemove",a),document.removeEventListener("mouseup",i)}return e.addEventListener("mousedown",n),()=>{e.removeEventListener("mousedown",n),document.removeEventListener("mousemove",a),document.removeEventListener("mouseup",i)}}var Mt=`
.emboss-bar-ghost {
  opacity: 0.7;
  pointer-events: none;
  z-index: 100;
}
`,Ie=null,ve=new Set,Rt=/^EMB-([A-Z]+)-(\d{8})-([a-f0-9]+)$/i,Nt={organize:"O",columns:"C",people:"P",subtasks:"S",analyze:"A"},Tt="emboss-2026";function zt(e){let t=4294967295;for(let s=0;s<e.length;s++){t^=e.charCodeAt(s);for(let n=0;n<8;n++)t=t>>>1^(t&1?3988292384:0)}return((t^4294967295)>>>0).toString(16).padStart(8,"0")}function Ht(e,t){return zt(Tt+"-"+e.toUpperCase()+"-"+t)}function It(e){Ie=e}function He(e){if(!Ie)return ve.has(e)||(ve.add(e),console.warn(`[Emboss] The "${e}" bundle requires a license. Get one at https://emboss.dev/pricing — your chart will work fine without it, but please support the project.`)),!1;const t=Rt.exec(Ie);if(!t)return ve.has("format")||(ve.add("format"),console.warn("[Emboss] Invalid license key format. Expected EMB-{FLAGS}-{YYYYMMDD}-{checksum}.")),!1;const[,s,n]=t,a=s.toUpperCase(),i=Ht(a,n);if(t[3].toLowerCase()!==i)return ve.has("checksum")||(ve.add("checksum"),console.warn("[Emboss] Invalid license key checksum.")),!1;const r=Nt[e];if(!r)return!1;if(e==="columns")return a.includes("O");if(!a.includes(r))return!1;const d=At(n);return d&&d<new Date&&(ve.has("expired")||(ve.add("expired"),console.warn(`[Emboss] Your license key expired on ${n.slice(0,4)}-${n.slice(4,6)}-${n.slice(6,8)}. Please renew at https://emboss.dev/pricing`))),!0}function At(e){const t=parseInt(e.slice(0,4),10),s=parseInt(e.slice(4,6),10)-1,n=parseInt(e.slice(6,8),10),a=new Date(t,s,n);return isNaN(a.getTime())?null:a}var Pt=`
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
`,Wt=class{constructor(e,t,s={}){this.extensions=[],this.listeners={},this.sidebarRenderers={},this.barRenderers={},this.headerRenderer=null,this.headerEl=null,this.bodyEl=null,this.gridEl=null,this.barsEl=null,this.spacerEl=null,this.dragCleanup=null;const n=document.querySelector(e);if(!n)throw new Error(`Emboss: no element found for "${e}"`);this.container=n,this.options=s;const a=s.startDate?new Date(s.startDate):new Date;if(a.setHours(0,0,0,0),this.state=ct(t,a),s.licenseKey&&It(s.licenseKey),s.view&&(this.state.view=s.view),s.density&&(s.density==="working"||He("organize"))&&(this.state.density=s.density),s.theme&&(this.state.theme=s.theme),s.moveDependencies&&(this.state.moveDependencies=!0),this.state.scale=xe(this.state.view,this.state.density,t,a),this.injectStyles("core",Pt+vt+Ct+Dt+Mt),s.extensions)for(const i of s.extensions)this.use(i);this.render()}get rows(){return this.state.rows}injectStyles(e,t){const s=document.createElement("style");s.textContent=t,s.dataset.emboss=e,document.head.appendChild(s)}use(e){if(!(e.type==="paid"&&e.bundle&&!He(e.bundle))){if(this.extensions.push(e),e.sidebarRenderer&&Object.assign(this.sidebarRenderers,e.sidebarRenderer),e.barRenderer&&Object.assign(this.barRenderers,e.barRenderer),e.headerRenderer&&(this.headerRenderer=e.headerRenderer),e.styles){const t=document.createElement("style");t.textContent=e.styles,t.dataset.embossExt=e.name,document.head.appendChild(t)}e.init&&e.init(this)}}remove(e){const t=this.extensions.findIndex(n=>n.name===e);if(t===-1)return;this.extensions.splice(t,1);const s=document.head.querySelector(`[data-emboss-ext="${e}"]`);s&&s.remove(),this.sidebarRenderers={},this.barRenderers={},this.headerRenderer=null;for(const n of this.extensions)n.sidebarRenderer&&Object.assign(this.sidebarRenderers,n.sidebarRenderer),n.barRenderer&&Object.assign(this.barRenderers,n.barRenderer),n.headerRenderer&&(this.headerRenderer=n.headerRenderer);this.render()}setView(e){this.state.view=e,this.state.scale=xe(e,this.state.density,this.state.rows,this.state.scale.startDate),this.emit("onViewChange",e),this.render()}setDensity(e){e!=="working"&&!He("organize")||(this.state.density=e,this.state.scale=xe(this.state.view,e,this.state.rows,this.state.scale.startDate),this.emit("onDensityChange",e),this.render())}setTheme(e){this.state.theme=e,this.container.classList.remove("emboss-grayscale","emboss-dark"),this.container.classList.add(`emboss-${e}`),this.emit("onThemeChange",e),this.render()}toggleCollapse(e){this.state.collapsed[e]=!this.state.collapsed[e],ze(this.state);const t=this.state.rows.find(s=>s.id===e);t&&this.emit("onCollapse",t,this.state.collapsed[e]),this.render()}updateRow(e,t){const s=this.state.rows.find(a=>a.id===e);!s||this.emit("onRowUpdate",s,t)===!1||(t.progress!==void 0&&!t.status&&(t.progress>=100?t.status="done":t.progress>0&&s.status!=="active"&&(t.status="active")),Object.assign(s,t),this.state.scale=xe(this.state.view,this.state.density,this.state.rows,this.state.scale.startDate),this.render())}addRow(e,t){if(t){const s=this.state.rows.findIndex(n=>n.id===t);s>=0?this.state.rows.splice(s+1,0,e):this.state.rows.push(e)}else this.state.rows.push(e);ze(this.state),this.state.scale=xe(this.state.view,this.state.density,this.state.rows,this.state.scale.startDate),this.render()}removeRow(e){this.state.rows=this.state.rows.filter(t=>t.id!==e);for(const t of this.state.rows)t.children&&(t.children=t.children.filter(s=>s!==e));ze(this.state),this.state.scale=xe(this.state.view,this.state.density,this.state.rows,this.state.scale.startDate),this.render()}on(e,t){this.listeners[e]||(this.listeners[e]=[]),this.listeners[e].push(t)}emit(e,...t){for(const s of this.extensions){const n=s.handlers?.[e];if(n&&n(...t)===!1)return!1}for(const s of this.listeners[e]??[])if(s(...t)===!1)return!1}render(){let e=this.state.rows;for(const x of this.extensions)x.enrichRows&&(e=x.enrichRows(e,this.state));this.emit("onBeforeRender",e,this.state),this.headerEl||this.createSkeleton();const t=e.filter(x=>!x.hidden),{scale:s}=this.state,n=this.state.density==="presentation",a=n?32:s.rowHeight,i=x=>x.type==="phase"?a:s.rowHeight,r=s.totalDays*s.dayWidth,d=t.reduce((x,E)=>x+i(E),0);this.container.dataset.density=this.state.density,this.container.classList.toggle("emboss-dense",this.state.density==="dense"),this.container.classList.toggle("emboss-presentation",this.state.density==="presentation"),this.container.classList.contains(`emboss-${this.state.theme}`)||(this.container.classList.remove("emboss-grayscale","emboss-dark"),this.container.classList.add(`emboss-${this.state.theme}`));const p=this.headerRenderer?this.headerRenderer(s,this.state):yt(s,this.state);this.headerEl.innerHTML="",this.headerEl.appendChild(p),p.style.minWidth=`${r}px`;const f=t.map(x=>i(x)),g=St(s,this.state,t.length,f);this.gridEl.innerHTML="",this.gridEl.appendChild(g);const b=document.createDocumentFragment();let c=0;t.forEach(x=>{const E=this.barRenderers[x.type],v=E?E(x,s,this.state,this.container):pt(x,s,this.state,this.container),D=i(x);if(x.type==="phase"){const N=n?7:5;v.style.top=`${c+Math.round((D-N)/2)}px`}else{const N=Math.round((D-s.barHeight)/2);v.style.top=`${c+N}px`}c+=D,b.appendChild(v)}),this.barsEl.innerHTML="",this.barsEl.appendChild(b),this.spacerEl.style.width=`${r}px`,this.spacerEl.style.height=`${d}px`,this.barsEl.style.width=`${r}px`,this.barsEl.style.height=`${d}px`,this.gridEl.style.width=`${r}px`,this.gridEl.style.height=`${d}px`,this.emit("afterRender",this.container,s,this.state)}createSkeleton(){this.container.classList.add("emboss"),this.headerEl=document.createElement("div"),this.headerEl.className="emboss-header",this.bodyEl=document.createElement("div"),this.bodyEl.className="emboss-body",this.gridEl=document.createElement("div"),this.gridEl.className="emboss-grid",this.barsEl=document.createElement("div"),this.barsEl.className="emboss-bars",this.spacerEl=document.createElement("div"),this.spacerEl.className="emboss-spacer",this.bodyEl.appendChild(this.spacerEl),this.bodyEl.appendChild(this.gridEl),this.bodyEl.appendChild(this.barsEl),this.container.innerHTML="",this.container.appendChild(this.headerEl),this.container.appendChild(this.bodyEl),this.bodyEl.addEventListener("scroll",()=>{this.headerEl.scrollLeft=this.bodyEl.scrollLeft}),this.dragCleanup=Lt(this.barsEl,{emit:(e,...t)=>this.emit(e,...t),updateRow:(e,t)=>this.updateRow(e,t),getState:()=>this.state}),this.barsEl.addEventListener("mouseover",e=>{const t=e.target.closest(".emboss-bar[data-id]");if(t){const s=t.dataset.id,n=this.state.rows.find(a=>a.id===s)??null;n&&this.state.hoveredRow!==n.id&&(this.state.hoveredRow=n.id,this.emit("onHover",n))}}),this.barsEl.addEventListener("mouseout",e=>{const t=e.target.closest(".emboss-bar[data-id]"),n=e.relatedTarget?.closest(".emboss-bar[data-id]");t&&!n&&(this.state.hoveredRow=null,this.emit("onHover",null))}),this.barsEl.addEventListener("click",e=>{const t=e.target.closest(".emboss-bar[data-id]");if(t){const s=t.dataset.id,n=this.state.rows.find(a=>a.id===s);n&&(this.state.selected=n.id,this.emit("onClick",n,e))}})}destroy(){this.dragCleanup&&this.dragCleanup(),document.querySelectorAll("[data-emboss],[data-emboss-ext]").forEach(e=>e.remove()),this.container.innerHTML="",this.container.classList.remove("emboss"),this.headerEl=null,this.bodyEl=null,this.gridEl=null,this.barsEl=null,this.spacerEl=null,this.listeners={}}},Ae=["#3b82f6","#8b5cf6","#ec4899","#f59e0b","#10b981","#ef4444","#06b6d4","#f97316"];function ge(e,t){let s;if(e.type==="phase")s=e;else if(e.parentId){const a=t.find(i=>i.id===e.parentId);a?.type==="phase"?s=a:a?.parentId&&(s=t.find(i=>i.id===a.parentId&&i.type==="phase"))}if(!s)return null;if(s.phaseColor)return s.phaseColor;const n=t.filter(a=>a.type==="phase").indexOf(s);return Ae[(n>=0?n:0)%Ae.length]}function Se(e,t){return e.type==="phase"&&t.density==="presentation"?32:t.scale.rowHeight}var Be=["#3b82f6","#8b5cf6","#ec4899","#f59e0b","#10b981","#ef4444","#06b6d4","#f97316"];function _t(e){let t=0;for(const s of e)t=(t<<5)-t+s.charCodeAt(0)|0;return Be[Math.abs(t)%Be.length]}function qt(e){const t=e.trim().split(/\s+/);return t.length===1?t[0][0].toUpperCase():(t[0][0]+t[t.length-1][0]).toUpperCase()}function Ge(e,t,s=22){const n=document.createElement("div");if(n.className=s===18?"emboss-avatar emboss-avatar-sm":"emboss-avatar",t){const i=e.assigneeColor||(e.assignee?_t(e.assignee):"");i&&(n.style.background=i)}const a=document.createElement("span");return a.className="emboss-avatar-initials",a.textContent=qt(e.assignee||""),n.appendChild(a),n}function Ne(){const e=document.createElement("span");return e.className="emboss-sidebar-grip",e.innerHTML='<svg width="6" height="10" viewBox="0 0 6 10"><circle cx="1.5" cy="1.5" r="1" fill="currentColor"/><circle cx="4.5" cy="1.5" r="1" fill="currentColor"/><circle cx="1.5" cy="5" r="1" fill="currentColor"/><circle cx="4.5" cy="5" r="1" fill="currentColor"/><circle cx="1.5" cy="8.5" r="1" fill="currentColor"/><circle cx="4.5" cy="8.5" r="1" fill="currentColor"/></svg>',e}var me=!1;function Xe(e,t){const s=document.createElement("div");s.className="emboss-sidebar-cell emboss-sidebar-task",s.dataset.id=e.id,s.style.height=`${t.scale.rowHeight}px`,s.style.paddingLeft=`${48+e.depth*16}px`;const n=document.createElement("span");if(n.className="emboss-sidebar-dot",me){const p=ge(e,t.rows);p&&(n.style.background=p)}const a=document.createElement("span");a.className="emboss-sidebar-name",a.textContent=e.name;const i=document.createElement("span");i.className="emboss-sidebar-delete",i.textContent="×";const r=document.createElement("span");r.className="emboss-sidebar-add-child",r.textContent="+",r.dataset.addParent=e.id;const d=document.createElement("div");return d.className="emboss-sidebar-name-area",d.appendChild(a),e.assignee&&d.appendChild(Ge(e,me,22)),d.appendChild(r),d.appendChild(i),s.prepend(Ne()),s.appendChild(n),s.appendChild(d),s}function Ot(e,t){const s=t.collapsed[e.id],n=e.children?e.children.length:0,a=document.createElement("div");a.className="emboss-sidebar-cell emboss-sidebar-phase",a.dataset.id=e.id,a.style.height=`${Se(e,t)}px`,a.style.paddingLeft=`${16+e.depth*16}px`;const i=document.createElement("span");i.className="emboss-sidebar-chevron",s&&i.classList.add("collapsed");const r=document.createElement("span");if(r.className="emboss-sidebar-pill",r.dataset.phaseId=e.id,me){const c=ge(e,t.rows);c&&(r.style.backgroundColor=c)}const d=document.createElement("span");d.className="emboss-sidebar-name emboss-sidebar-phase-name",d.textContent=e.name;const p=document.createElement("span");p.className="emboss-sidebar-badge",p.textContent=String(n);const f=document.createElement("span");f.className="emboss-sidebar-delete",f.textContent="×";const g=document.createElement("span");g.className="emboss-sidebar-add-child",g.textContent="+",g.dataset.addParent=e.id;const b=document.createElement("div");return b.className="emboss-sidebar-name-area",b.appendChild(d),b.appendChild(p),b.appendChild(g),b.appendChild(f),a.prepend(Ne()),a.appendChild(i),a.appendChild(r),a.appendChild(b),a}function Bt(e,t){const s=document.createElement("div");s.className="emboss-sidebar-cell emboss-sidebar-subtask",s.dataset.id=e.id,s.style.height=`${t.scale.rowHeight}px`,s.style.paddingLeft=`${48+e.depth*16}px`;const n=document.createElement("span");if(n.className="emboss-sidebar-dot emboss-sidebar-dot-sm",me){const d=ge(e,t.rows);d&&(n.style.background=d)}const a=document.createElement("span");a.className="emboss-sidebar-name",a.textContent=e.name;const i=document.createElement("span");i.className="emboss-sidebar-delete",i.textContent="×";const r=document.createElement("div");return r.className="emboss-sidebar-name-area",r.appendChild(a),e.assignee&&r.appendChild(Ge(e,me,22)),r.appendChild(i),s.prepend(Ne()),s.appendChild(n),s.appendChild(r),s}function jt(e,t){const s=document.createElement("div");s.className="emboss-sidebar-cell emboss-sidebar-milestone",s.dataset.id=e.id,s.style.height=`${t.scale.rowHeight}px`,s.style.paddingLeft=`${48+e.depth*16}px`;const n=document.createElement("span");if(n.className="emboss-sidebar-diamond",me){const d=ge(e,t.rows);d&&(n.style.borderColor=d)}const a=document.createElement("span");a.className="emboss-sidebar-name emboss-sidebar-milestone-name",a.textContent=e.name;const i=document.createElement("span");i.className="emboss-sidebar-delete",i.textContent="×";const r=document.createElement("div");return r.className="emboss-sidebar-name-area",r.appendChild(a),r.appendChild(i),s.prepend(Ne()),s.appendChild(n),s.appendChild(r),s}function Yt(e,t){const s=document.createElement("div");s.className="emboss-sidebar-cell emboss-sidebar-rail-cell emboss-sidebar-phase",s.dataset.id=e.id,s.style.height=`${Se(e,t)}px`;const n=document.createElement("span");if(n.className="emboss-rail-pill",n.textContent=e.name.charAt(0).toUpperCase(),me){const a=ge(e,t.rows);a&&(n.style.background=a)}return s.appendChild(n),s}function Ut(e,t){const s=document.createElement("div");s.className="emboss-sidebar-cell emboss-sidebar-rail-cell emboss-sidebar-task",s.dataset.id=e.id,s.style.height=`${t.scale.rowHeight}px`;const n=document.createElement("span");if(n.className="emboss-sidebar-dot",me){const a=ge(e,t.rows);a&&(n.style.background=a)}return s.appendChild(n),s}function Ft(e,t){const s=document.createElement("div");s.className="emboss-sidebar-cell emboss-sidebar-rail-cell emboss-sidebar-subtask",s.dataset.id=e.id,s.style.height=`${t.scale.rowHeight}px`;const n=document.createElement("span");if(n.className="emboss-sidebar-dot emboss-sidebar-dot-sm",me){const a=ge(e,t.rows);a&&(n.style.background=a)}return s.appendChild(n),s}function Vt(e,t){const s=document.createElement("div");s.className="emboss-sidebar-cell emboss-sidebar-rail-cell emboss-sidebar-milestone",s.dataset.id=e.id,s.style.height=`${t.scale.rowHeight}px`;const n=document.createElement("span");if(n.className="emboss-sidebar-diamond",me){const a=ge(e,t.rows);a&&(n.style.borderColor=a)}return s.appendChild(n),s}var je={task:Xe,phase:Ot,subtask:Bt,milestone:jt},Gt={task:Ut,phase:Yt,subtask:Ft,milestone:Vt},Xt={name:"sidebar",type:"paid",bundle:"organize",sidebarRenderer:je,init(e){let t=null,s=null,n=null,a=null,i=null,r=null,d=!1,p=!1,f=null,g=!1;function b(){n&&(n.remove(),n=null)}function c(){a&&(a.remove(),a=null,i=null)}function x(){const l=e.state.selected;if(l){const u=e.state.rows.find(m=>m.id===l);if(u?.type==="phase")return u;if(u?.parentId){const m=e.state.rows.find(y=>y.id===u.parentId);if(m?.type==="phase")return m}}return e.state.rows.find(u=>u.type==="phase")||null}function E(l){if(n){b();return}c(),n=document.createElement("div"),n.className="emboss-add-menu";const u=[{label:"Add Phase",type:"phase"},{label:"Add Task",type:"task"},{label:"Add Milestone",type:"milestone"}];for(const y of u){const w=document.createElement("div");w.className="emboss-add-menu-item",w.dataset.addType=y.type,w.textContent=y.label,n.appendChild(w)}const m=l.getBoundingClientRect();n.style.top=`${m.bottom+4}px`,n.style.left=`${m.right-160}px`,document.body.appendChild(n),requestAnimationFrame(()=>{document.addEventListener("mousedown",v)})}function v(l){const u=l.target;u.closest(".emboss-sidebar-add-btn")||n&&!n.contains(u)&&(b(),document.removeEventListener("mousedown",v))}function D(l){b(),document.removeEventListener("mousedown",v);const u=`new-${Date.now()}`,m=x();if(r=u,d=!0,l==="phase"){const y={id:u,type:"phase",name:"New Phase",depth:0,parentId:null,collapsed:!1,hidden:!1,start:0,duration:14,progress:0,status:"upcoming",dependencies:[],children:[]};e.addRow(y)}else if(l==="task"){const y={id:u,type:"task",name:"New Task",depth:m?1:0,parentId:m?.id||null,collapsed:!1,hidden:!1,start:m?m.start:0,duration:5,progress:0,status:"upcoming",dependencies:[]};m?.children&&m.children.push(u);const w=m?N(m.id):null;e.addRow(y,w||m?.id)}else{const y={id:u,type:"milestone",name:"New Milestone",depth:m?1:0,parentId:m?.id||null,collapsed:!1,hidden:!1,start:m?m.start+m.duration:0,duration:0,progress:0,status:"upcoming",dependencies:[]};m?.children&&m.children.push(u);const w=m?N(m.id):null;e.addRow(y,w||m?.id)}}function N(l){const u=e.state.rows,m=u.findIndex(k=>k.id===l);if(m===-1)return null;const y=new Set([l]);let w=null;for(let k=m+1;k<u.length;k++){const $=u[k].parentId;if($&&y.has($))y.add(u[k].id),w=u[k].id;else break}return w}function P(l){const u=e.state.rows.find(T=>T.id===l);if(!u)return;const m=`new-${Date.now()}`,y=u.type==="phase",w=y?"task":"subtask",k=u.depth+1;r=m,d=!0;const $={id:m,type:w,name:y?"New Task":"New Subtask",depth:k,parentId:u.id,collapsed:!1,hidden:!1,start:u.start,duration:5,progress:0,status:"upcoming",dependencies:[]};u.children||(u.children=[]),u.children.push(m),e.state.collapsed[l]&&e.toggleCollapse(l);const A=N(l);e.addRow($,A||l)}function M(l){const u=e.state.rows.find(y=>y.id===l);if(!u)return;r===l&&(r=null,d=!1);const m=new Set([l]);if(u.children?.length)for(const y of u.children){m.add(y);const w=e.state.rows.find(k=>k.id===y);w?.children?.length&&w.children.forEach(k=>m.add(k))}e.state.rows=e.state.rows.filter(y=>!m.has(y.id));for(const y of e.state.rows)y.children&&(y.children=y.children.filter(w=>!m.has(w)));e.render()}function z(l,u){if(!(f?.classList.contains("emboss-vivid")??!1))return;if(a&&i===u){c();return}c(),b(),i=u;const y=e.state.rows.find(A=>A.id===u);if(!y)return;const w=(y.phaseColor||"").toLowerCase();a=document.createElement("div"),a.className="emboss-color-picker";const k=document.createElement("div");k.className="emboss-color-grid";for(const A of Ae){const T=document.createElement("div");T.className="emboss-color-swatch",T.dataset.color=A,T.style.background=A,A.toLowerCase()===w&&T.classList.add("active"),k.appendChild(T)}a.appendChild(k);const $=l.getBoundingClientRect();a.style.top=`${$.bottom+4}px`,a.style.left=`${$.left}px`,document.body.appendChild(a),requestAnimationFrame(()=>{document.addEventListener("mousedown",Y)})}function Y(l){const u=l.target;u.closest(".emboss-sidebar-pill")||a&&!a.contains(u)&&(c(),document.removeEventListener("mousedown",Y))}function o(l,u){const m=e.state.rows.find(T=>T.id===u);if(!m)return;const y=d&&r===u;r=u;const w=m.name,k=document.createElement("input");k.className="emboss-sidebar-edit-input",k.value=m.name,k.style.fontSize=m.type==="phase"?"13px":"12.5px",k.style.fontWeight=m.type==="phase"?"600":"400",m.type==="phase"&&k.classList.add("emboss-sidebar-edit-phase"),l.textContent="",l.appendChild(k),k.focus(),k.select();let $=!1;function A(){if($)return;$=!0;const T=k.value.trim();r=null,d=!1,k.parentElement&&k.remove(),T?T!==w?e.updateRow(u,{name:T}):e.render():y?M(u):e.render()}k.addEventListener("blur",()=>{requestAnimationFrame(()=>{k.parentElement&&A()})}),k.addEventListener("keydown",T=>{T.key==="Enter"&&(T.preventDefault(),A()),T.key==="Escape"&&($=!0,r=null,d=!1,k.parentElement&&k.remove(),y?M(u):e.render())})}e.on("afterRender",(l,u,m)=>{if(f=l,me=l.classList.contains("emboss-vivid"),l.classList.toggle("emboss-sidebar-collapsed",g),!t){let h=function(I,R){for(let L=I-1;L>=0;L--)if(R[L].type==="phase")return R[L];return null},V=function(I,R){for(let L=I+1;L<R.length;L++)if(R[L].type==="phase")return L;return R.length},J=function(I,R,L){if(R===0)return null;if(R===1){for(let O=I-1;O>=0;O--)if(L[O].type==="phase")return L[O];return null}for(let O=I-1;O>=0;O--){if(L[O].type==="phase")return null;if(L[O].type==="task"&&L[O].depth===1)return L[O]}return null},_=function(I,R,L,O){const H=L.indexOf(I);if(R===H||R===H+1)return!1;const ue=O??I.depth;if(ue===0){if(I.type==="phase"){const Ee=V(H,L);if(R>=H&&R<=Ee)return!1}const pe=L[R];return!pe||pe.type==="phase"}return ue===1?h(R,L)!==null:ue===2?J(R,2,L)!==null:!1},B=function(I,R){const L=e.state.rows,O=L.filter(U=>!U.hidden),H=L.find(U=>U.id===I);if(!H)return;const ue=[H];if(H.type==="phase"&&H.children?.length)for(const U of L)H.children.includes(U.id)&&ue.push(U);const pe=O[R],Ee=pe?L.indexOf(pe):L.length,De=new Set(ue.map(U=>U.id)),le=L.filter(U=>!De.has(U.id));let ce=Ee;for(let U=0;U<Ee;U++)De.has(L[U].id)&&ce--;if(le.splice(ce,0,...ue),e.state.rows=le,ae!==null&&ae!==H.depth){const U=ae===0?"phase":ae===1?"task":"subtask",be=H.parentId&&le.find(ke=>ke.id===H.parentId)||null,he=J(le.indexOf(H),ae,le);if(e.emit("onRowReparent",H,be,he,U)===!1){e.render();return}be?.children&&(be.children=be.children.filter(ke=>ke!==H.id));const Te=H.children||[];if(H.depth=ae,H.type=U,H.parentId=he?.id||null,he&&(he.children||(he.children=[]),he.children.push(H.id)),U==="phase"&&!H.children&&(H.children=[]),U==="phase"&&Te.length){for(const ke of Te){const $e=le.find(Ke=>Ke.id===ke);$e&&($e.depth=1,$e.type="task",$e.parentId=H.id)}H.children=Te}}else if(H.type==="task"||H.type==="milestone"){const U=h(le.indexOf(H),le);if(U&&U.id!==H.parentId){const be=le.find(he=>he.id===H.parentId);be?.children&&(be.children=be.children.filter(he=>he!==H.id)),H.parentId=U.id,H.depth=1,U.children||(U.children=[]),U.children.push(H.id)}}e.emit("onRowReorder",I,ce),e.render()},C=function(I){p=!0,te=I;const R=e.state.rows.find(O=>O.id===I);if(R&&(ae=R.depth),oe=s.querySelector(`[data-id="${I}"]`),!oe)return;const L=oe.getBoundingClientRect();ye=ne-L.top,ie=oe.cloneNode(!0),ie.classList.add("emboss-drag-ghost"),ie.style.position="fixed",ie.style.width=`${L.width}px`,ie.style.left=`${L.left}px`,ie.style.top=`${L.top}px`,ie.style.pointerEvents="none",ie.style.zIndex="1000",document.body.appendChild(ie),oe.classList.add("emboss-sidebar-dragging"),re=document.createElement("div"),re.className="emboss-drop-indicator",re.style.display="none",s.appendChild(re),document.addEventListener("mousemove",S),document.addEventListener("mouseup",q),document.addEventListener("keydown",W)},S=function(I){if(!p||!s||!te)return;ie&&(ie.style.top=`${I.clientY-ye}px`);const R=s.getBoundingClientRect(),L=I.clientY-R.top+s.scrollTop,O=e.state.rows.filter(ce=>!ce.hidden);let H=0,ue=O.length;for(let ce=0;ce<O.length;ce++){const U=Se(O[ce],e.state);if(L<H+U/2){ue=ce;break}H+=U}ue=Math.max(0,Math.min(ue,O.length));const pe=e.state.rows.find(ce=>ce.id===te);if(!pe)return;const Ee=I.clientX-K,De=Math.round(Ee/40);let le=Math.max(0,Math.min(2,pe.depth+De));if(pe.type==="phase"&&pe.children?.length&&(le=0),le>0&&!J(ue,le,O)&&(le=pe.depth),ae=le,_(pe,ue,O,ae)){de=ue,re.style.display="block";const ce=O.slice(0,ue).reduce((U,be)=>U+Se(be,e.state),0);re.style.top=`${ce}px`,re.style.left=`${16+ae*16}px`}else de=null,re.style.display="none"},q=function(){p&&de!==null&&te&&B(te,de),F()},W=function(I){I.key==="Escape"&&F()},F=function(){p=!1,oe&&oe.classList.remove("emboss-sidebar-dragging"),ie&&ie.remove(),ie=null,ye=0,K=0,ae=null,re&&re.remove(),re=null,te=null,oe=null,de=null,ee=null,document.removeEventListener("mousemove",S),document.removeEventListener("mouseup",q),document.removeEventListener("keydown",W)};t=document.createElement("div"),t.className="emboss-sidebar-header",s=document.createElement("div"),s.className="emboss-sidebar";const G=l.querySelector(".emboss-header"),Z=l.querySelector(".emboss-body");l.insertBefore(t,G),l.insertBefore(s,Z),l.classList.add("emboss-has-sidebar"),Z.addEventListener("scroll",()=>{s.scrollTop=Z.scrollTop}),t.addEventListener("click",I=>{const R=I.target;if(R.closest(".emboss-sidebar-collapse")){g=!g,l.classList.toggle("emboss-sidebar-collapsed",g),e.render();return}R.closest(".emboss-sidebar-add-btn")&&E(R.closest(".emboss-sidebar-add-btn"))}),s.addEventListener("click",I=>{const R=I.target;if(R.closest(".emboss-sidebar-add-child")){I.stopPropagation();const H=R.closest(".emboss-sidebar-add-child");H.dataset.addParent&&P(H.dataset.addParent);return}if(R.closest(".emboss-sidebar-delete")){I.stopPropagation();const H=R.closest(".emboss-sidebar-cell");H?.dataset.id&&M(H.dataset.id);return}const L=R.closest(".emboss-sidebar-phase");if(L&&L.dataset.id){if(R.closest(".emboss-sidebar-chevron")){I.stopPropagation(),e.toggleCollapse(L.dataset.id);return}if(R.closest(".emboss-sidebar-name")){I.stopPropagation(),o(R.closest(".emboss-sidebar-name"),L.dataset.id);return}if(R.closest(".emboss-sidebar-pill")){I.stopPropagation(),z(R.closest(".emboss-sidebar-pill"),L.dataset.id);return}return}const O=R.closest(".emboss-sidebar-cell");O?.dataset.id&&R.closest(".emboss-sidebar-name")&&(I.stopPropagation(),o(R.closest(".emboss-sidebar-name"),O.dataset.id))}),document.body.addEventListener("click",I=>{const R=I.target,L=R.closest(".emboss-add-menu-item");L?.dataset.addType&&D(L.dataset.addType);const O=R.closest(".emboss-color-swatch");O?.dataset.color&&i&&(e.updateRow(i,{phaseColor:O.dataset.color}),c(),document.removeEventListener("mousedown",Y))});let j=null,ne=0,K=0,ae=null,ee=null,te=null,oe=null,re=null,de=null,ie=null,ye=0;s.addEventListener("mousedown",I=>{const L=I.target.closest(".emboss-sidebar-grip");if(!L)return;const O=L.closest(".emboss-sidebar-cell[data-id]");O?.dataset.id&&(ee=O.dataset.id,ne=I.clientY,K=I.clientX,j=window.setTimeout(()=>{j=null,ee&&C(ee)},150))}),s.addEventListener("mousemove",I=>{j&&ee&&(Math.abs(I.clientY-ne)>5||Math.abs(I.clientX-K)>5)&&(clearTimeout(j),j=null,C(ee),ee=null)}),s.addEventListener("mouseup",()=>{j&&(clearTimeout(j),j=null,ee=null)})}t.innerHTML="";const y=document.createElement("div");y.className="emboss-sidebar-header-area";const w=document.createElement("span");w.className="emboss-sidebar-header-label",w.textContent="Tasks",y.appendChild(w);const k=document.createElement("button");k.className="emboss-sidebar-add-btn",k.textContent="+",y.appendChild(k);const $=document.createElement("button");if($.className="emboss-sidebar-collapse",$.textContent=g?"▶":"◀",y.appendChild($),t.appendChild(y),s.querySelector(".emboss-sidebar-edit-input")||p)return;const T=m.rows.filter(h=>!h.hidden),Q=g?Gt:je,se=document.createDocumentFragment();for(const h of T){const V=Q[h.type],J=V?V(h,m):Xe(h,m);J&&se.appendChild(J)}if(s.innerHTML="",s.appendChild(se),r){const h=s.querySelector(`[data-id="${r}"]`);if(h){const V=h.querySelector(".emboss-sidebar-name");V&&(o(V,r),h.scrollIntoView({block:"nearest"}))}}const X=l.querySelector(".emboss-bars");if(X&&(X.querySelectorAll(".emboss-inline-phase").forEach(h=>h.remove()),g)){const h=m.density==="dense";let V=0;T.forEach((J,_)=>{const B=Se(J,e.state);if(J.type!=="phase"){V+=B;return}const C=document.createElement("div");C.className="emboss-inline-phase",C.dataset.id=J.id,C.style.top=`${V}px`,C.style.height=`${B}px`;const S=document.createElement("span");S.className="emboss-inline-chevron",S.textContent=m.collapsed[J.id]?"▶":"▼";const q=document.createElement("span");if(q.className="emboss-inline-phase-name",q.textContent=J.name,me){const W=ge(J,m.rows);W&&(q.style.color=W)}if(C.appendChild(S),C.appendChild(q),!h){const W=J.children?.length||0;if(W>0){const F=document.createElement("span");F.className="emboss-inline-phase-count",F.textContent=String(W),C.appendChild(F)}}S.addEventListener("click",W=>{W.stopPropagation(),e.toggleCollapse(J.id)}),X.appendChild(C),V+=B})}})},styles:`
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
`},Kt={name:"phases",type:"paid",bundle:"organize",barRenderer:{},init(e){}},Ye=["#3b82f6","#8b5cf6","#ec4899","#f59e0b","#10b981","#ef4444","#06b6d4","#f97316"];function Qt(e,t,s,n){const a=s.density==="dense",i=e.start*t.dayWidth,r=Math.round(t.rowHeight/2),d=s.density==="presentation"?24:a?14:20,p=d/2,f=e.parentId?s.rows.find(v=>v.id===e.parentId&&v.type==="phase"):null,g=f?s.rows.filter(v=>v.type==="phase").indexOf(f):0,b=g>=0?g:0,c=f?.phaseColor||Ye[b%Ye.length],x=document.createElement("div");x.className="emboss-milestone",x.dataset.id=e.id,x.dataset.status=e.status,x.style.cssText=`left:${i-p}px;top:${r-p}px;width:${d}px;height:${d}px;`,x.style.setProperty("--phase-c",c);const E=document.createElement("div");if(E.className="emboss-milestone-diamond",e.progress>0){const v=document.createElement("div");v.className="emboss-milestone-fill",v.style.height=`${e.progress}%`,E.appendChild(v)}if(x.appendChild(E),a)(n?.classList.contains("emboss-has-sidebar")??!1)||(x.addEventListener("mouseenter",()=>{const D=document.createElement("div");D.className="emboss-dense-tag",D.textContent=e.name,D.style.left=`${i}px`,D.style.top=`${r-p-18}px`,x._denseTag=D,x.parentElement?.appendChild(D)}),x.addEventListener("mouseleave",()=>{x._denseTag?.remove(),x._denseTag=null}));else{const v=document.createElement("div");v.className="emboss-milestone-label",v.style.fontSize=`${t.labelSize}px`,v.textContent=e.name,x.appendChild(v)}return x}var Jt={name:"milestones",type:"paid",bundle:"organize",enrichRows(e){return e.map(t=>{if(t.type!=="milestone"||!t.dependencies.length)return t;const s=t.dependencies.map(d=>e.find(p=>p.id===d)).filter(Boolean);if(!s.length)return t;const n=Math.round(s.reduce((d,p)=>d+p.progress,0)/s.length),a=s.every(d=>d.status==="done"),i=s.some(d=>d.status==="active"||d.status==="done"),r=a?"done":i?"active":t.status;return{...t,progress:n,status:r}})},barRenderer:{milestone:Qt},styles:`
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
`},Zt={name:"inline-edit",type:"paid",bundle:"organize",init(e){}};function Le(e,t){const s=new Date(e);return s.setDate(s.getDate()+t),s}function Ue(e,t){return Math.round((t.getTime()-e.getTime())/864e5)}function Fe(e){const t=e.getFullYear(),s=String(e.getMonth()+1).padStart(2,"0"),n=String(e.getDate()).padStart(2,"0");return`${t}-${s}-${n}`}function es(e,t,s){if(s)return`${e.getMonth()+1}/${e.getDate()} – ${t.getMonth()+1}/${t.getDate()}`;const n=e.toLocaleString("en",{month:"short"}),a=t.toLocaleString("en",{month:"short"}),i=e.getDate(),r=t.getDate(),d=e.getFullYear(),p=t.getFullYear();return d!==p?`${n} ${i} ‘${String(d).slice(2)} – ${a} ${r} ‘${String(p).slice(2)}`:n===a?`${n} ${i} – ${r}`:`${n} ${i} – ${a} ${r}`}function ts(e,t){return t?`${e.getMonth()+1}/${e.getDate()}`:`${e.toLocaleString("en",{month:"short"})} ${e.getDate()}`}var ss={duration:76,dates:120},ns={duration:"DURATION",dates:"DATES"},as={name:"columns",type:"paid",bundle:"columns",init(e){if(!(e.extensions?e.extensions.some(d=>d.name==="sidebar"):!1)){console.info("Emboss: Columns requires the Organize extension.");return}let s=e.options?.sidebar?.columns||[],n=null;function a(){n&&(n.remove(),n=null)}e.setSidebarColumns=d=>{s=d,e.render()},e.on("afterRender",(d,p,f)=>{if(s.length===0){d.style.removeProperty("--emboss-sidebar-w");return}const g=d.classList.contains("emboss-sidebar-collapsed"),b=f.density==="dense",c=f.density==="presentation",x=b?{duration:56,dates:96}:c?{duration:66,dates:100}:ss,E=s.reduce((M,z)=>M+(x[z]||0),0);if(g){d.style.removeProperty("--emboss-sidebar-w");return}const v=b?220:280;d.style.setProperty("--emboss-sidebar-w",`${v+E}px`);const D=d.querySelector(".emboss-sidebar-header");if(D){D.querySelectorAll(".emboss-sidebar-header-col").forEach(M=>M.remove());for(const M of s){const z=document.createElement("div");z.className=`emboss-sidebar-header-col emboss-sidebar-col-${M}`,z.textContent=ns[M]||M.toUpperCase(),D.appendChild(z)}}const N=d.querySelector(".emboss-sidebar");if(!N)return;N.querySelectorAll(".emboss-sidebar-cell").forEach(M=>{const z=M,Y=z.dataset.id;if(!Y)return;const o=f.rows.find(l=>l.id===Y);if(o){z.querySelectorAll(".emboss-sidebar-col").forEach(l=>l.remove());for(const l of s){const u=document.createElement("div");if(u.className=`emboss-sidebar-col ${l}`,l==="duration")o.type==="milestone"?u.textContent="—":o.type==="phase"?u.textContent="":(u.textContent=`${o.duration}d`,u.classList.add("editable"),u.addEventListener("click",m=>{m.stopPropagation(),a(),i(u,o,e)}));else if(l==="dates")if(o.type==="phase")u.textContent="";else{const m=Le(p.startDate,o.start);if(o.type==="milestone"||o.duration===0)u.textContent=ts(m,b);else{const y=Le(p.startDate,o.start+o.duration);u.textContent=es(m,y,b)}u.classList.add("editable"),u.addEventListener("click",y=>{y.stopPropagation(),r(u,o,e,p.startDate)})}z.appendChild(u)}}})});function i(d,p,f){const g=p.duration,b=document.createElement("input");b.type="number",b.className="emboss-column-input",b.value=String(p.duration),b.min="1",b.style.width="40px",b.style.textAlign="right";let c=!1;function x(){if(c)return;c=!0;const E=parseInt(b.value),v=E&&E>0?E:g;b.parentElement&&b.remove(),f.updateRow(p.id,{duration:v})}b.addEventListener("blur",()=>{requestAnimationFrame(x)}),b.addEventListener("keydown",E=>{E.key==="Enter"&&(E.preventDefault(),b.blur()),E.key==="Escape"&&(c=!0,b.parentElement&&b.remove(),f.render())}),d.textContent="",d.appendChild(b),b.focus(),b.select()}function r(d,p,f,g){a();const b=Le(g,p.start),c=Le(g,p.start+p.duration);n=document.createElement("div"),n.className="emboss-date-popover";const x=document.createElement("input");x.type="date",x.className="emboss-date-input",x.value=Fe(b);const E=document.createElement("span");E.className="emboss-date-arrow",E.textContent="→";const v=document.createElement("input");v.type="date",v.className="emboss-date-input",v.value=Fe(c),p.type==="milestone"||p.duration===0?n.appendChild(x):(n.appendChild(x),n.appendChild(E),n.appendChild(v));const D=d.getBoundingClientRect();n.style.top=`${D.bottom+4}px`,n.style.left=`${D.left}px`,document.body.appendChild(n);function N(){const P=Ue(g,new Date(x.value));if(p.type==="milestone"||p.duration===0)f.updateRow(p.id,{start:P});else{const M=Ue(g,new Date(v.value)),z=Math.max(1,M-P);f.updateRow(p.id,{start:P,duration:z})}a()}x.addEventListener("change",N),v.addEventListener("change",N),requestAnimationFrame(()=>{document.addEventListener("mousedown",function P(M){const z=M.target;n&&!n.contains(z)&&z!==d&&(a(),document.removeEventListener("mousedown",P))})})}},styles:`
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
`},fe=window.leantime||(window.leantime={});fe.embossAdapter=function(){function e(o){var l=new Date(o);return l.setHours(0,0,0,0),l}function t(o,l){return Math.round((l.getTime()-o.getTime())/864e5)}function s(o,l){var u=new Date(o);return u.setDate(u.getDate()+l),u}function n(o){var l=o.getFullYear(),u=String(o.getMonth()+1).padStart(2,"0"),m=String(o.getDate()).padStart(2,"0");return l+"-"+u+"-"+m+" 00:00:00"}var a="#00b893",i=["#8D99A6","#124F7D"];function r(o){return!o||o.trim()===""||o==="null"||o.indexOf("var(")===0||i.indexOf(o.toUpperCase())!==-1||i.indexOf(o)!==-1?a:o}var d={Day:"day",Week:"week",Month:"month",Quarter:"quarter"},p={day:"Day",week:"Week",month:"Month",quarter:"Quarter"};function f(o){for(var l=null,u=0;u<o.length;u++){var m=e(o[u].start);(!l||m<l)&&(l=m)}return l||(l=new Date),s(l,-7)}function g(o,l){var u=e(o.start),m=e(o.end),y=t(l,u),w=Math.max(7,t(u,m)),k=[];o.dependencies&&o.dependencies!==""&&(k=String(o.dependencies).split(",").map(function(X){return X.trim()}).filter(Boolean));var $=parseFloat(o.progress)||0,A="task";o.type==="milestone"?A="phase":o.type==="subtask"&&(A="subtask");var T=0,Q=null;o.type!=="milestone"&&(o.milestoneid&&String(o.milestoneid)!=="0"&&String(o.milestoneid)!==""?(Q=String(o.milestoneid),T=1):k.length>0&&(Q=k[0],T=1));var se="upcoming";return $>=100?se="done":$>0&&(se="active"),{id:String(o.id),type:A,name:o.name||"",start:y,duration:w,progress:$,depth:T,parentId:Q,collapsed:!1,hidden:!1,status:se,dependencies:k,phaseColor:r(o.bg_color),_ltId:o.id,_ltType:o.type,_ltProjectName:o.projectName||null,_ltSortIndex:o.sortIndex,_ltThumbnail:o.thumbnail||null}}function b(o,l){var u=e(o.end),m=t(l,u),y=parseFloat(o.progress)||0,w="upcoming";return y>=100?w="done":y>0&&(w="active"),{id:String(o.id)+"-end",type:"milestone",name:o.name||"",start:m,duration:0,progress:y,depth:1,parentId:String(o.id),collapsed:!1,hidden:!1,status:w,dependencies:[String(o.id)],_ltId:o.id,_ltType:o.type,_ltProjectName:o.projectName||null,_ltSortIndex:o.sortIndex,_ltThumbnail:o.thumbnail||null,_ltIsDiamond:!0}}function c(o){for(var l=f(o),u=[],m={},y=[],w={},k=[],$=0;$<o.length;$++){var A=o[$];if(A.type==="milestone"){var T=String(A.id);m[T]=A,y.push(T),w[T]||(w[T]=[])}}for(var $=0;$<o.length;$++){var A=o[$];if(A.type!=="milestone"){var Q=null;if(A.milestoneid&&String(A.milestoneid)!=="0"&&String(A.milestoneid)!==""){var T=String(A.milestoneid);m[T]&&(Q=T)}if(!Q&&A.dependencies&&A.dependencies!=="")for(var se=String(A.dependencies).split(","),X=0;X<se.length;X++){var h=se[X].trim();if(m[h]){Q=h;break}}Q?(w[Q]||(w[Q]=[]),w[Q].push(A)):k.push(A)}}for(var V=0;V<y.length;V++){var T=y[V],J=m[T];u.push(g(J,l));for(var _=w[T]||[],B=0;B<_.length;B++)u.push(g(_[B],l));u.push(b(J,l))}for(var $=0;$<k.length;$++)u.push(g(k[$],l));for(var C={},$=0;$<u.length;$++)u[$].type==="phase"&&(C[u[$].id]=u[$]);for(var $=0;$<u.length;$++)u[$].parentId&&C[u[$].parentId]&&(C[u[$].parentId].children||(C[u[$].parentId].children=[]),C[u[$].parentId].children.push(u[$].id));return{rows:u,projectStart:l}}function x(o){for(var l=f(o),u=[],m=0;m<o.length;m++){var y=o[m],w=e(y.start),k=e(y.end),$=t(l,w),A=Math.max(7,t(w,k)),T=parseFloat(y.progress)||0,Q=[];y.dependencies&&y.dependencies!==""&&y.dependencies!=="-1"&&(Q=String(y.dependencies).split(",").map(function(X){return X.trim()}).filter(Boolean));var se="upcoming";T>=100?se="done":T>0&&(se="active"),u.push({id:String(y.id),type:"task",name:y.name||"",start:$,duration:A,progress:T,depth:0,parentId:null,collapsed:!1,hidden:!1,status:se,dependencies:Q,phaseColor:r(y.bg_color),_ltId:y.id,_ltType:y.type,_ltProjectName:y.projectName||null,_ltSortIndex:y.sortIndex,_ltThumbnail:y.thumbnail||null})}return{rows:u,projectStart:l}}function E(o,l){return n(s(l,o))}function v(o,l,u,m){fe.ticketsRepository.updateMilestoneDates(o,l,u,m)}function D(o,l,u,m){fetch(fe.appUrl+"/api/projects",{method:"PATCH",body:new URLSearchParams({id:o,start:l,end:u,sortIndex:m}),credentials:"include",headers:{"Content-Type":"application/x-www-form-urlencoded","X-Requested-With":"XMLHttpRequest"}})}function N(o,l){for(var u=l||fe.appUrl+"/api/tickets",m={action:"ganttSort",payload:{}},y=0;y<o.length;y++)m.payload[o[y].id]=y+1;var w={};w.action=m.action;for(var k in m.payload)m.payload.hasOwnProperty(k)&&(w["payload["+k+"]"]=m.payload[k]);fetch(u,{method:"POST",body:new URLSearchParams(w),credentials:"include",headers:{"Content-Type":"application/x-www-form-urlencoded","X-Requested-With":"XMLHttpRequest"}})}function P(o,l){var u=p[l]||l;fe.usersRepository.updateUserViewSettings(o,u)}function M(o){if(o){var l=o._ltId||o.id,u=o._ltType||o.type,m=l,y=u,w=String(l).split("-");w.length>1&&(w[0]==="pgm"?(y="project",m=w[1]):w[0]==="ticket"&&(y="ticket",m=w[1])),y==="milestone"||u==="milestone"?fe.modals.openByUrl("/tickets/editMilestone/"+m):y==="project"?fe.modals.openByUrl("/projects/showProject/"+m):fe.modals.openByUrl("/tickets/showTicket/"+m)}}function z(o,l,u,m,y){var w=document.querySelector(o+" .emboss-bars"),k=document.querySelector(o+" .emboss-body");if(!w)return;function $(){for(var _=w.querySelectorAll(".emboss-bar-phase"),B=l.state.rows,C=0;C<_.length;C++){var S=_[C],q=S.dataset.id,W=B.find(function(de){return de.id===q});if(!S.querySelector(".emboss-phase-handle")){var F=document.createElement("div");F.className="emboss-phase-handle emboss-phase-handle-left";var G=document.createElement("div");G.className="emboss-phase-handle emboss-phase-handle-right",S.appendChild(F),S.appendChild(G)}if(W){var Z=B.filter(function(de){return de.parentId===q&&de.type!=="milestone"&&!de._ltIsDiamond}),j=0;if(Z.length>0){for(var ne=0,K=0;K<Z.length;K++)ne+=parseFloat(Z[K].progress)||0;j=Math.round(ne/Z.length)}else j=parseFloat(W.progress)||0;W.progress=j;var ae=q+"-end",ee=B.find(function(de){return de.id===ae});ee&&(ee.progress=j,ee.status=j>=100?"done":j>0?"active":"upcoming");var te=S.querySelector(".emboss-phase-fill");te||(te=document.createElement("div"),te.className="emboss-phase-fill",S.insertBefore(te,S.firstChild)),te.style.width=Math.min(100,Math.max(0,j))+"%";var oe=S.querySelector(".emboss-bar-label");if(oe&&W.name){var re=j>0&&j<100?" · "+j+"%":j>=100?" · Done":"";oe.textContent=W.name+re}}}}$(),l.on("onRender",$);function A(_){var B=_.id+"-end",C=l.state.rows.find(function(S){return S.id===B});C&&(C.start=_.start+_.duration)}function T(){return l.state.scale.rowHeight||40}function Q(){return l.state.rows.filter(function(_){return!_.hidden})}function se(_){var B=T(),C=Math.floor(_/B),S=Q();return Math.max(0,Math.min(C,S.length-1))}var X=document.createElement("div");X.className="emboss-reorder-indicator",X.style.cssText="position:absolute;left:0;right:0;height:2px;background:var(--accent1,#004766);z-index:50;pointer-events:none;display:none;",w.appendChild(X);var h=null,V=5,J=1.8;w.addEventListener("mousedown",function(_){if(_.button===0){var B=_.target.closest(".emboss-phase-handle"),C=_.target.closest(".emboss-bar[data-id]");if(C){var S=C.classList.contains("emboss-bar-phase"),q=C.dataset.id,W=l.state.rows.find(function(j){return j.id===q});if(W&&!W._ltIsDiamond){var F="pending";S&&B&&(B.classList.contains("emboss-phase-handle-left")?F="resize-left":B.classList.contains("emboss-phase-handle-right")&&(F="resize-right")),(S||F!=="pending")&&(_.preventDefault(),_.stopPropagation());var G=Q(),Z=G.indexOf(W);h={row:W,barEl:C,isPhase:S,intent:F,startX:_.clientX,startY:_.clientY,originalStart:W.start,originalDuration:W.duration,dayWidth:l.state.scale.dayWidth,rowHeight:T(),originalIndex:Z,_newStart:null,_newDuration:null,_newIndex:null,ghost:null,coreCancelled:!1}}}}}),document.addEventListener("mousemove",function(_){if(h){var B=_.clientX-h.startX,C=_.clientY-h.startY,S=Math.abs(B),q=Math.abs(C);if(h.intent==="pending"){var W=Math.max(S,q);if(W<V)return;if(q>S*J&&q>V){if(h.intent="reorder",!h.isPhase&&!h.coreCancelled){h.coreCancelled=!0,h.barEl.style.opacity="";var F=w.querySelector(".emboss-bar-ghost");F&&F.remove()}}else if(h.isPhase)h.intent="move-h";else{h=null;return}}if(_.preventDefault(),h.intent==="reorder"){h.ghost||(h.ghost=h.barEl.cloneNode(!0),h.ghost.classList.add("emboss-bar-ghost"),h.ghost.style.pointerEvents="none",h.ghost.style.zIndex="100",w.appendChild(h.ghost),h.barEl.style.opacity="0.3");var G=h.originalIndex*h.rowHeight;h.ghost.style.top=G+C+"px";var Z=w.getBoundingClientRect(),j=_.clientY-Z.top+(k?k.scrollTop:0),ne=se(j);h._newIndex=ne,X.style.top=ne*h.rowHeight+"px",X.style.display="block";return}var K=Math.round(B/h.dayWidth);if(h.intent==="move-h"){var ae=h.originalStart+K;h.barEl.style.left=ae*h.dayWidth+"px",h._newStart=ae,h._newDuration=h.originalDuration}else if(h.intent==="resize-right"){var ee=Math.max(1,h.originalDuration+K);h.barEl.style.width=ee*h.dayWidth+"px",h._newStart=h.originalStart,h._newDuration=ee}else if(h.intent==="resize-left"){var te=h.originalStart+K,oe=Math.max(1,h.originalDuration-K);h.barEl.style.left=te*h.dayWidth+"px",h.barEl.style.width=oe*h.dayWidth+"px",h._newStart=te,h._newDuration=oe}}}),document.addEventListener("mouseup",function(_){if(h){if(h.ghost&&h.ghost.remove(),h.barEl.style.opacity="",X.style.display="none",h.intent==="reorder"&&h._newIndex!==null&&h._newIndex!==h.originalIndex){var B=Q(),C=h.row,S=l.state.rows,q=S.indexOf(C),W=B[h._newIndex],F=W?S.indexOf(W):S.length-1;S.splice(q,1),F>q&&F--,S.splice(F,0,C);for(var G=null,Z=F-1;Z>=0;Z--){if(S[Z].type==="phase"){G=S[Z];break}if(S[Z].depth===0)break}if(C.type!=="phase"){var j=C.parentId,ne=G?G.id:null;if(j!==ne){if(j){var K=S.find(function(ye){return ye.id===j});K&&K.children&&(K.children=K.children.filter(function(ye){return ye!==C.id}))}C.parentId=ne,C.depth=ne?1:0,G&&(G.children||(G.children=[]),G.children.push(C.id))}}var ae=y||(m==="project"?fe.appUrl+"/api/projects":null);N(S,ae),l.render(),h=null;return}if(h.isPhase&&(h.intent==="move-h"||h.intent==="resize-left"||h.intent==="resize-right")){var C=h.row,ee=h._newStart!==null?h._newStart:C.start,te=h._newDuration!==null?h._newDuration:C.duration;if(ee!==h.originalStart||te!==h.originalDuration){C.start=ee,C.duration=te,A(C);var oe=E(ee,u),re=E(ee+te,u),de=C._ltId||C.id,ie=String(de).split("-");ie.length>1&&ie[0]==="pgm"?D(ie[1],oe,re,0):m==="project"?D(de,oe,re,0):v(de,oe,re,0),l.render()}}h=null}}),l.on("onDragEnd",function(_){if(_.parentId){var B=l.state.rows.find(function(C){return C.id===_.parentId&&C.type==="phase"});B&&A(B)}})}function Y(o,l,u){u=u||{};var m=u.entityType||"ticket",y=m==="project"?x(l):c(l),w=y.rows,k=y.projectStart,$=d[u.viewMode]||u.viewMode||"month",A=u.readonly||!1,T=u.viewSettingKey||"roadmap",Q=u.sortApiUrl||null,se=m==="project",X=[Ze,dt,it];se||(X.push(Kt),X.push(Jt)),A||(X.push(Xt),se||(X.push(Zt),X.push(as)));var h=document.querySelector(o);h&&h.classList.add("emboss-vivid");var V=new Wt(o,w,{licenseKey:"EMB-OCPSA-20301231-4a736e3f",view:$,density:"working",startDate:k,extensions:X,moveDependencies:!0});V.on("onDragEnd",function(S,q){if(A||S._ltIsDiamond)return!1;var W=q.start!==void 0?q.start:S.start,F=q.duration!==void 0?q.duration:S.duration,G=E(W,k),Z=E(W+F,k),j=S._ltId||S.id,ne=w.indexOf(S)+1,K=String(j).split("-");K.length>1&&K[0]==="pgm"?D(K[1],G,Z,ne):K.length>1&&K[0]==="ticket"?v(K[1],G,Z,ne):m==="project"?D(j,G,Z,ne):v(j,G,Z,ne)}),V.on("onClick",function(S,q){M(S)});var J=document.querySelector(o+" .emboss-bars");J&&J.addEventListener("click",function(S){var q=S.target.closest(".emboss-milestone[data-id], .emboss-bar-phase[data-id]");if(q){var W=q.dataset.id,F=V.state.rows.find(function(G){return G.id===W});F&&M(F)}}),A||z(o,V,k,m,Q),V.on("onViewChange",function(S){P(T,S)}),V.on("onRowReorder",function(S,q){var W=Q||(m==="project"?fe.appUrl+"/api/projects":null);N(V.rows,W)});var _=document.querySelector(o+" .emboss-sidebar"),B=document.querySelector(o+" .emboss-body");_&&B&&_.addEventListener("scroll",function(){B.scrollTop=_.scrollTop});var C=document.querySelector("#ganttTimeControl");return C&&C.addEventListener("click",function(S){var q=S.target.closest("a");if(q){var W=q.getAttribute("data-value"),F=d[W]||W.toLowerCase();V.setView(F),C.querySelectorAll("a").forEach(function(G){G.classList.remove("active")}),q.classList.add("active"),document.querySelectorAll(".viewText").forEach(function(G){G.textContent=q.textContent.trim()})}}),V}return{init:Y,convertTasks:c}}();
