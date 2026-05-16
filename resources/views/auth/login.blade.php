<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — {{ config('sso.app_name') }}</title>
    {{-- Geist — same font used by the portal --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/geist@1.3.1/dist/fonts/geist-sans/style.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: "Geist", -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            background: #000;
            min-height: 100vh;
            overflow: hidden;
        }

        /* WebGL animated lines — same as login/page.tsx <FloatingLines> */
        #bg-canvas {
            position: fixed;
            inset: 0;
            width: 100%;
            height: 100%;
            display: block;
        }

        /* bg-black/30 overlay */
        .overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.3);
            pointer-events: none;
        }

        /* relative z-10 flex min-h-screen items-center justify-center px-4 py-12 */
        .page {
            position: relative;
            z-index: 10;
            display: flex;
            min-height: 100vh;
            align-items: center;
            justify-content: center;
            padding: 3rem 1rem;
        }

        /* w-full max-w-105 = 26.25rem ≈ 420px */
        .card-outer {
            width: 100%;
            max-width: 26.25rem;
        }

        /* rounded-2xl border border-white/10 bg-black/40 p-8 shadow-2xl backdrop-blur-xl */
        .card {
            width: 100%;
            border-radius: 1rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(0, 0, 0, 0.4);
            padding: 2rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            backdrop-filter: blur(24px);
            -webkit-backdrop-filter: blur(24px);
        }

        /* mb-8 text-center — logo block */
        .logo-section {
            margin-bottom: 2rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        /* mb-3 inline-flex size-12 items-center justify-center rounded-xl
           bg-gradient-to-br from-[#E945F5] to-[#2F4BC0] shadow-lg */
        .logo-icon {
            margin-bottom: 0.75rem;
            display: inline-flex;
            width: 3rem;
            height: 3rem;
            align-items: center;
            justify-content: center;
            border-radius: 0.75rem;
            background: linear-gradient(135deg, #E945F5, #2F4BC0);
            box-shadow: 0 10px 15px -3px rgba(0,0,0,.1), 0 4px 6px -4px rgba(0,0,0,.1);
        }

        /* size-6 text-white */
        .logo-icon svg { width: 1.5rem; height: 1.5rem; color: #fff; }

        /* text-2xl font-bold tracking-tight text-white */
        .logo-title {
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: -0.025em;
            color: #fff;
        }

        /* mt-1 text-sm text-white/50 */
        .logo-sub {
            margin-top: 0.25rem;
            font-size: 0.875rem;
            color: rgba(255, 255, 255, 0.5);
        }

        /* mb-4 rounded-lg border border-red-500/20 bg-red-500/10 px-4 py-3 text-sm text-red-400 */
        .alert-error {
            margin-bottom: 1rem;
            border-radius: 0.5rem;
            border: 1px solid rgba(239, 68, 68, 0.2);
            background: rgba(239, 68, 68, 0.1);
            padding: 0.75rem 1rem;
            font-size: 0.875rem;
            color: #f87171;
        }

        /* space-y-4 */
        .form-fields { display: flex; flex-direction: column; gap: 1rem; }

        /* space-y-1.5 */
        .form-group { display: flex; flex-direction: column; gap: 0.375rem; }

        /*
         * Label: text-xs/relaxed font-medium text-white/70
         * text-xs = 0.75rem, line-height relaxed = 1.625
         */
        .form-label {
            font-size: 0.75rem;
            line-height: 1.625;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.7);
        }

        .input-wrap { position: relative; }

        /*
         * Input: h-7 px-2 py-0.5 rounded-md text-xs/relaxed
         * h-7    = 1.75rem = 28px
         * px-2   = 0.5rem  =  8px
         * py-0.5 = 0.125rem=  2px
         * border-white/10 bg-white/5 text-white placeholder:text-white/30
         * focus-visible:border-[#E945F5]/50 focus-visible:ring-2 focus-visible:ring-[#E945F5]/20
         */
        .form-input {
            width: 100%;
            height: 1.75rem;
            min-width: 0;
            border-radius: 0.375rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(255, 255, 255, 0.05);
            padding: 0.125rem 0.5rem;
            font-size: 0.75rem;
            line-height: 1.625;
            font-family: inherit;
            color: #fff;
            outline: none;
            transition: border-color 0.15s, box-shadow 0.15s;
            -webkit-appearance: none;
        }
        .form-input::placeholder { color: rgba(255, 255, 255, 0.3); }
        .form-input:focus {
            border-color: rgba(233, 69, 245, 0.5);    /* focus-visible:border-[#E945F5]/50 */
            box-shadow: 0 0 0 2px rgba(233, 69, 245, 0.2); /* ring-2 ring-[#E945F5]/20 */
        }
        .form-input.pr-10 { padding-right: 2.5rem; }

        /*
         * Password toggle:
         * absolute right-3 top-1/2 -translate-y-1/2 text-white/40 hover:text-white/70
         */
        .pw-toggle {
            position: absolute;
            right: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: rgba(255, 255, 255, 0.4);
            display: flex;
            align-items: center;
            padding: 0;
            line-height: 0;
            transition: color 0.15s;
        }
        .pw-toggle:hover { color: rgba(255, 255, 255, 0.7); }

        /*
         * Button: mt-2 w-full h-7 rounded-md px-2 text-xs/relaxed font-semibold
         * bg-gradient-to-r from-[#E945F5] to-[#2F4BC0] text-white hover:opacity-90
         */
        .btn-submit {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.25rem;
            width: 100%;
            margin-top: 0.5rem;
            height: 1.75rem;
            border-radius: 0.375rem;
            border: none;
            padding: 0 0.5rem;
            background: linear-gradient(90deg, #E945F5, #2F4BC0);
            color: #fff;
            font-size: 0.75rem;
            line-height: 1.625;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            white-space: nowrap;
            transition: opacity 0.2s;
        }
        .btn-submit:hover  { opacity: 0.9; }
        .btn-submit:active { opacity: 0.75; }

        /* divider — mt-4 flex items-center gap-3 text-xs text-white/30 */
        .divider {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-top: 1rem;
            font-size: 0.75rem;
            color: rgba(255, 255, 255, 0.3);
        }
        .divider::before, .divider::after {
            content: '';
            flex: 1;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* OAuth button */
        .sso-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            height: 1.75rem;
            padding: 0 0.5rem;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 0.375rem;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            font-size: 0.75rem;
            line-height: 1.625;
            font-family: inherit;
            margin-bottom: 0.5rem;
            transition: background 0.15s, border-color 0.15s;
        }
        .sso-btn:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.2);
        }

        /* footer register link */
        .footer-link {
            margin-top: 1.5rem;
            text-align: center;
            font-size: 0.75rem;
            line-height: 1.625;
            color: rgba(255, 255, 255, 0.5);
        }
        .footer-link a {
            color: rgba(255, 255, 255, 0.8);
            text-decoration: underline;
            text-underline-offset: 2px;
        }
    </style>
</head>
<body>

<canvas id="bg-canvas"></canvas>
<div class="overlay"></div>

<div class="page">
    <div class="card-outer">
        <div class="card">

            {{-- Logo — same as LoginForm.tsx --}}
            <div class="logo-section">
                <div class="logo-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M16.5 9.4 7.55 4.24"/>
                        <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                        <polyline points="3.29 7 12 12 20.71 7"/>
                        <line x1="12" y1="22" x2="12" y2="12"/>
                    </svg>
                </div>
                <h1 class="logo-title">{{ config('sso.app_name') }}</h1>
                <p class="logo-sub">Sign in to your account</p>
            </div>

            @if ($errors->any())
                <div class="alert-error">{{ $errors->first() }}</div>
            @endif

            @if (config('sso.form_auth.enabled', false))
            <form method="POST" action="{{ route('sso.login') }}" novalidate>
                @csrf
                @if (!empty($redirectTo))
                    <input type="hidden" name="redirect_to" value="{{ $redirectTo }}">
                @endif

                <div class="form-fields">
                    <div class="form-group">
                        <label class="form-label" for="email">Email</label>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            class="form-input"
                            value="{{ old('email') }}"
                            placeholder="you@example.com"
                            autocomplete="email"
                            required
                            autofocus
                        >
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="password">Password</label>
                        <div class="input-wrap">
                            <input
                                id="password"
                                type="password"
                                name="password"
                                class="form-input pr-10"
                                placeholder="••••••••"
                                autocomplete="current-password"
                                required
                            >
                            <button type="button" class="pw-toggle" onclick="togglePw()" aria-label="Show/hide password">
                                <svg id="eye-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn-submit">Sign In</button>
            </form>
            @endif

            @php $providers = array_filter(config('sso.providers', []), fn($p) => !empty($p['client_id'])); @endphp

            @if (config('sso.form_auth.enabled', false) && count($providers))
                <div class="divider">or continue with</div>
            @endif

            @foreach ($providers as $provider => $cfg)
                <a href="{{ route('sso.redirect', $provider) }}" class="sso-btn">
                    Sign in with {{ ucfirst($provider) }}
                </a>
            @endforeach

            @if (config('sso.form_auth.allow_register', true) && config('sso.form_auth.enabled', false))
                <p class="footer-link">
                    Don't have an account? <a href="{{ route('sso.register.form') }}">Register</a>
                </p>
            @endif

        </div>
    </div>
</div>

<script>
(function () {
    var VS = '#version 300 es\nin vec2 a_pos;\nvoid main(){gl_Position=vec4(a_pos,0.0,1.0);}';

    var FS = [
        '#version 300 es',
        'precision highp float;',
        'out vec4 outColor;',
        'uniform float iTime;',
        'uniform vec3  iResolution;',
        'uniform float animationSpeed;',
        'uniform bool  enableTop,enableMiddle,enableBottom;',
        'uniform int   topLineCount,middleLineCount,bottomLineCount;',
        'uniform float topLineDistance,middleLineDistance,bottomLineDistance;',
        'uniform vec3  topWavePosition,middleWavePosition,bottomWavePosition;',
        'uniform vec2  iMouse;',
        'uniform bool  interactive;',
        'uniform float bendRadius,bendStrength,bendInfluence;',
        'uniform bool  parallax;',
        'uniform float parallaxStrength;',
        'uniform vec2  parallaxOffset;',
        'uniform vec3  lineGradient[8];',
        'uniform int   lineGradientCount;',
        'mat2 rot(float r){return mat2(cos(r),sin(r),-sin(r),cos(r));}',
        'vec3 gc(float t){',
        '  if(lineGradientCount<=0)return vec3(0.0);',
        '  if(lineGradientCount==1)return lineGradient[0]*0.5;',
        '  float s=clamp(t,0.0,0.9999)*float(lineGradientCount-1);',
        '  int i=int(floor(s));float f=fract(s);',
        '  return mix(lineGradient[i],lineGradient[min(i+1,lineGradientCount-1)],f)*0.5;',
        '}',
        'float wave(vec2 uv,float off,vec2 sv,vec2 mv,bool bend){',
        '  float t=iTime*animationSpeed;',
        '  float y=sin(uv.x+off+t*0.1)*sin(off+t*0.2)*0.3;',
        '  if(bend){vec2 d=sv-mv;y+=(mv.y-sv.y)*exp(-dot(d,d)*bendRadius)*bendStrength*bendInfluence;}',
        '  float m=uv.y-y;return 0.0175/max(abs(m)+0.01,1e-3)+0.01;',
        '}',
        'void mainImage(out vec4 fc,in vec2 coord){',
        '  vec2 uv=(2.0*coord-iResolution.xy)/iResolution.y; uv.y*=-1.0;',
        '  if(parallax)uv+=parallaxOffset;',
        '  vec3 col=vec3(0.0);',
        '  vec2 mu=vec2(0.0);',
        '  if(interactive){mu=(2.0*iMouse-iResolution.xy)/iResolution.y;mu.y*=-1.0;}',
        '  if(enableBottom)for(int i=0;i<12;++i){if(i>=bottomLineCount)break;',
        '    float fi=float(i),t=fi/max(float(bottomLineCount-1),1.0);',
        '    vec2 rv=uv*rot(bottomWavePosition.z*log(length(uv)+1.0));',
        '    col+=gc(t)*wave(rv+vec2(bottomLineDistance*fi+bottomWavePosition.x,bottomWavePosition.y),1.5+0.2*fi,uv,mu,interactive)*0.2;}',
        '  if(enableMiddle)for(int i=0;i<12;++i){if(i>=middleLineCount)break;',
        '    float fi=float(i),t=fi/max(float(middleLineCount-1),1.0);',
        '    vec2 rv=uv*rot(middleWavePosition.z*log(length(uv)+1.0));',
        '    col+=gc(t)*wave(rv+vec2(middleLineDistance*fi+middleWavePosition.x,middleWavePosition.y),2.0+0.15*fi,uv,mu,interactive);}',
        '  if(enableTop)for(int i=0;i<12;++i){if(i>=topLineCount)break;',
        '    float fi=float(i),t=fi/max(float(topLineCount-1),1.0);',
        '    vec2 rv=uv*rot(topWavePosition.z*log(length(uv)+1.0));rv.x*=-1.0;',
        '    col+=gc(t)*wave(rv+vec2(topLineDistance*fi+topWavePosition.x,topWavePosition.y),1.0+0.2*fi,uv,mu,interactive)*0.1;}',
        '  fc=vec4(col,1.0);',
        '}',
        'void main(){vec4 c=vec4(0.0);mainImage(c,gl_FragCoord.xy);outColor=c;}'
    ].join('\n');

    var canvas = document.getElementById('bg-canvas');
    var gl = canvas.getContext('webgl2');
    if (!gl) return;

    function sh(t,s){var x=gl.createShader(t);gl.shaderSource(x,s);gl.compileShader(x);return x;}
    var p=gl.createProgram();
    gl.attachShader(p,sh(gl.VERTEX_SHADER,VS));
    gl.attachShader(p,sh(gl.FRAGMENT_SHADER,FS));
    gl.linkProgram(p);gl.useProgram(p);

    var b=gl.createBuffer();
    gl.bindBuffer(gl.ARRAY_BUFFER,b);
    gl.bufferData(gl.ARRAY_BUFFER,new Float32Array([-1,-1,1,-1,-1,1,1,1]),gl.STATIC_DRAW);
    var a=gl.getAttribLocation(p,'a_pos');
    gl.enableVertexAttribArray(a);gl.vertexAttribPointer(a,2,gl.FLOAT,false,0,0);

    function u(n){return gl.getUniformLocation(p,n);}
    var U={t:u('iTime'),r:u('iResolution'),sp:u('animationSpeed'),
        et:u('enableTop'),em:u('enableMiddle'),eb:u('enableBottom'),
        tlc:u('topLineCount'),mlc:u('middleLineCount'),blc:u('bottomLineCount'),
        tld:u('topLineDistance'),mld:u('middleLineDistance'),bld:u('bottomLineDistance'),
        twp:u('topWavePosition'),mwp:u('middleWavePosition'),bwp:u('bottomWavePosition'),
        im:u('iMouse'),ia:u('interactive'),br:u('bendRadius'),bs:u('bendStrength'),bi:u('bendInfluence'),
        pl:u('parallax'),ps:u('parallaxStrength'),po:u('parallaxOffset'),
        lg:u('lineGradient[0]'),lgc:u('lineGradientCount')};

    gl.uniform1f(U.sp,1.0);
    gl.uniform1i(U.et,1);gl.uniform1i(U.em,1);gl.uniform1i(U.eb,1);
    gl.uniform1i(U.tlc,6);gl.uniform1i(U.mlc,6);gl.uniform1i(U.blc,6);
    gl.uniform1f(U.tld,0.05);gl.uniform1f(U.mld,0.05);gl.uniform1f(U.bld,0.05);
    gl.uniform3f(U.twp,10.0,0.5,-0.4);
    gl.uniform3f(U.mwp,5.0,0.0,0.2);
    gl.uniform3f(U.bwp,2.0,-0.7,-1.0);
    gl.uniform1i(U.ia,1);gl.uniform1f(U.br,1.0);gl.uniform1f(U.bs,-0.7);
    gl.uniform1i(U.pl,1);gl.uniform1f(U.ps,0.2);
    gl.uniform3fv(U.lg,new Float32Array([233/255,69/255,245/255,47/255,75/255,192/255,233/255,69/255,245/255,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1]));
    gl.uniform1i(U.lgc,3);

    var D=0.03,mx={tx:0,ty:0,cx:0,cy:0},inf={t:0,c:0},px={tx:0,ty:0,cx:0,cy:0};
    document.addEventListener('pointermove',function(e){
        var dpr=window.devicePixelRatio||1;
        mx.tx=e.clientX*dpr;mx.ty=(window.innerHeight-e.clientY)*dpr;inf.t=1.0;
        px.tx=(e.clientX-window.innerWidth/2)/window.innerWidth*0.2;
        px.ty=-(e.clientY-window.innerHeight/2)/window.innerHeight*0.2;
    });
    document.addEventListener('pointerleave',function(){inf.t=0;});
    function lr(a,b,t){return a+(b-a)*t;}

    var w0=0,h0=0,t0=null;
    function frame(ts){
        if(!t0)t0=ts;
        var dpr=window.devicePixelRatio||1,W=window.innerWidth,H=window.innerHeight;
        var cw=Math.round(W*dpr),ch=Math.round(H*dpr);
        if(cw!==w0||ch!==h0){canvas.width=cw;canvas.height=ch;canvas.style.width=W+'px';canvas.style.height=H+'px';gl.viewport(0,0,cw,ch);w0=cw;h0=ch;}
        mx.cx=lr(mx.cx,mx.tx,D);mx.cy=lr(mx.cy,mx.ty,D);
        inf.c=lr(inf.c,inf.t,D);px.cx=lr(px.cx,px.tx,D);px.cy=lr(px.cy,px.ty,D);
        gl.uniform1f(U.t,(ts-t0)*0.001);
        gl.uniform3f(U.r,cw,ch,1.0);
        gl.uniform2f(U.im,mx.cx,mx.cy);
        gl.uniform1f(U.bi,inf.c);
        gl.uniform2f(U.po,px.cx,px.cy);
        gl.drawArrays(gl.TRIANGLE_STRIP,0,4);
        requestAnimationFrame(frame);
    }
    requestAnimationFrame(frame);

    window.togglePw=function(){
        var i=document.getElementById('password'),ic=document.getElementById('eye-icon');
        if(i.type==='password'){
            i.type='text';
            ic.innerHTML='<path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/>';
        }else{
            i.type='password';
            ic.innerHTML='<path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>';
        }
    };
}());
</script>
</body>
</html>
