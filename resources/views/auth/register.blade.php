<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Account — {{ config('sso.app_name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #000;
            min-height: 100vh;
            overflow: hidden;
        }

        #bg-canvas {
            position: fixed;
            inset: 0;
            width: 100%;
            height: 100%;
            display: block;
        }

        .overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.3);
            pointer-events: none;
        }

        .page {
            position: relative;
            z-index: 10;
            display: flex;
            min-height: 100vh;
            align-items: center;
            justify-content: center;
            padding: 3rem 1rem;
        }

        .card-outer {
            width: 100%;
            max-width: 26.25rem;
        }

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

        .logo-section {
            margin-bottom: 2rem;
            text-align: center;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .logo-icon {
            margin-bottom: 0.75rem;
            display: inline-flex;
            width: 3rem;
            height: 3rem;
            align-items: center;
            justify-content: center;
            border-radius: 0.75rem;
            background: linear-gradient(135deg, #E945F5, #2F4BC0);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -4px rgba(0, 0, 0, 0.1);
        }

        .logo-icon svg {
            width: 1.5rem;
            height: 1.5rem;
            color: #fff;
        }

        .logo-title {
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: -0.025em;
            color: #fff;
        }

        .logo-sub {
            margin-top: 0.25rem;
            font-size: 0.875rem;
            color: rgba(255, 255, 255, 0.5);
        }

        .alert-error {
            margin-bottom: 1rem;
            border-radius: 0.5rem;
            border: 1px solid rgba(239, 68, 68, 0.2);
            background: rgba(239, 68, 68, 0.1);
            padding: 0.75rem 1rem;
            font-size: 0.875rem;
            color: #f87171;
        }

        .form-fields { display: flex; flex-direction: column; gap: 1rem; }
        .form-group  { display: flex; flex-direction: column; gap: 0.375rem; }

        .form-label {
            font-size: 0.875rem;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.7);
        }

        .input-wrap { position: relative; }

        .form-input {
            display: flex;
            width: 100%;
            height: 2.25rem;
            border-radius: 0.375rem;
            border: 1px solid rgba(255, 255, 255, 0.1);
            background: rgba(255, 255, 255, 0.05);
            padding: 0.25rem 0.75rem;
            font-size: 0.875rem;
            font-family: inherit;
            color: #fff;
            outline: none;
            transition: border-color 0.15s, box-shadow 0.15s;
            -webkit-appearance: none;
        }
        .form-input::placeholder { color: rgba(255, 255, 255, 0.3); }
        .form-input:focus {
            border-color: rgba(233, 69, 245, 0.5);
            box-shadow: 0 0 0 3px rgba(233, 69, 245, 0.2);
        }
        .form-input.pr-10 { padding-right: 2.5rem; }

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

        .btn-submit {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            margin-top: 0.5rem;
            height: 2.25rem;
            border-radius: 0.375rem;
            border: none;
            padding: 0.5rem 1rem;
            background: linear-gradient(90deg, #E945F5, #2F4BC0);
            color: #fff;
            font-size: 0.875rem;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            transition: opacity 0.2s;
        }
        .btn-submit:hover  { opacity: 0.9; }
        .btn-submit:active { opacity: 0.75; }

        .footer-link {
            margin-top: 1.5rem;
            text-align: center;
            font-size: 0.875rem;
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
                <p class="logo-sub">Create your account</p>
            </div>

            @if ($errors->any())
                <div class="alert-error">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('sso.register') }}" novalidate>
                @csrf
                @if (!empty($redirectTo))
                    <input type="hidden" name="redirect_to" value="{{ $redirectTo }}">
                @endif

                <div class="form-fields">
                    <div class="form-group">
                        <label class="form-label" for="name">Full Name</label>
                        <input
                            id="name"
                            type="text"
                            name="name"
                            class="form-input"
                            value="{{ old('name') }}"
                            placeholder="Your name"
                            autocomplete="name"
                            required
                            autofocus
                        >
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="email">Email</label>
                        <input
                            id="email"
                            type="email"
                            name="email"
                            class="form-input"
                            value="{{ old('email') }}"
                            placeholder="you@drsb.com"
                            autocomplete="email"
                            required
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
                                autocomplete="new-password"
                                required
                            >
                            <button type="button" class="pw-toggle" onclick="togglePw('password','eye-1')" aria-label="Show/hide password">
                                <svg id="eye-1" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="password_confirmation">Confirm Password</label>
                        <div class="input-wrap">
                            <input
                                id="password_confirmation"
                                type="password"
                                name="password_confirmation"
                                class="form-input pr-10"
                                placeholder="••••••••"
                                autocomplete="new-password"
                                required
                            >
                            <button type="button" class="pw-toggle" onclick="togglePw('password_confirmation','eye-2')" aria-label="Show/hide confirm password">
                                <svg id="eye-2" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn-submit">Create Account</button>
            </form>

            <p class="footer-link">
                Already have an account? <a href="{{ route('sso.login.form') }}">Sign In</a>
            </p>

        </div>
    </div>
</div>

<script>
(function () {
    var VS = [
        '#version 300 es',
        'in vec2 a_pos;',
        'void main() { gl_Position = vec4(a_pos, 0.0, 1.0); }'
    ].join('\n');

    var FS = [
        '#version 300 es',
        'precision highp float;',
        'out vec4 outColor;',
        'uniform float iTime;',
        'uniform vec3  iResolution;',
        'uniform float animationSpeed;',
        'uniform bool  enableTop;',
        'uniform bool  enableMiddle;',
        'uniform bool  enableBottom;',
        'uniform int   topLineCount;',
        'uniform int   middleLineCount;',
        'uniform int   bottomLineCount;',
        'uniform float topLineDistance;',
        'uniform float middleLineDistance;',
        'uniform float bottomLineDistance;',
        'uniform vec3  topWavePosition;',
        'uniform vec3  middleWavePosition;',
        'uniform vec3  bottomWavePosition;',
        'uniform vec2  iMouse;',
        'uniform bool  interactive;',
        'uniform float bendRadius;',
        'uniform float bendStrength;',
        'uniform float bendInfluence;',
        'uniform bool  parallax;',
        'uniform float parallaxStrength;',
        'uniform vec2  parallaxOffset;',
        'uniform vec3  lineGradient[8];',
        'uniform int   lineGradientCount;',
        'const vec3 BLACK = vec3(0.0);',
        'const vec3 PINK  = vec3(233.0,  69.0, 245.0) / 255.0;',
        'const vec3 BLUE  = vec3( 47.0,  75.0, 192.0) / 255.0;',
        'mat2 rot(float r) { return mat2(cos(r),sin(r),-sin(r),cos(r)); }',
        'vec3 background_color(vec2 uv) {',
        '    vec3 col=vec3(0.0); float y=sin(uv.x-0.2)*0.3-0.1; float m=uv.y-y;',
        '    col+=mix(BLUE,BLACK,smoothstep(0.0,1.0,abs(m)));',
        '    col+=mix(PINK,BLACK,smoothstep(0.0,1.0,abs(m-0.8)));',
        '    return col*0.5;',
        '}',
        'vec3 getLineColor(float t,vec3 b) {',
        '    if(lineGradientCount<=0) return b;',
        '    if(lineGradientCount==1) return lineGradient[0]*0.5;',
        '    float ct=clamp(t,0.0,0.9999); float sc=ct*float(lineGradientCount-1);',
        '    int idx=int(floor(sc)); float f=fract(sc); int idx2=min(idx+1,lineGradientCount-1);',
        '    return mix(lineGradient[idx],lineGradient[idx2],f)*0.5;',
        '}',
        'float wave(vec2 uv,float off,vec2 sv,vec2 mv,bool bend) {',
        '    float time=iTime*animationSpeed;',
        '    float amp=sin(off+time*0.2)*0.3;',
        '    float y=sin(uv.x+off+time*0.1)*amp;',
        '    if(bend){vec2 d=sv-mv;float inf=exp(-dot(d,d)*bendRadius);y+=(mv.y-sv.y)*inf*bendStrength*bendInfluence;}',
        '    float m=uv.y-y; return 0.0175/max(abs(m)+0.01,1e-3)+0.01;',
        '}',
        'void mainImage(out vec4 fragColor,in vec2 fragCoord){',
        '    vec2 baseUv=(2.0*fragCoord-iResolution.xy)/iResolution.y; baseUv.y*=-1.0;',
        '    if(parallax) baseUv+=parallaxOffset;',
        '    vec3 col=vec3(0.0); vec3 b=(lineGradientCount>0)?vec3(0.0):background_color(baseUv);',
        '    vec2 mouseUv=vec2(0.0);',
        '    if(interactive){mouseUv=(2.0*iMouse-iResolution.xy)/iResolution.y; mouseUv.y*=-1.0;}',
        '    if(enableBottom){for(int i=0;i<12;++i){if(i>=bottomLineCount)break;',
        '        float fi=float(i); float t=fi/max(float(bottomLineCount-1),1.0);',
        '        float a=bottomWavePosition.z*log(length(baseUv)+1.0); vec2 rv=baseUv*rot(a);',
        '        col+=getLineColor(t,b)*wave(rv+vec2(bottomLineDistance*fi+bottomWavePosition.x,bottomWavePosition.y),1.5+0.2*fi,baseUv,mouseUv,interactive)*0.2;}}',
        '    if(enableMiddle){for(int i=0;i<12;++i){if(i>=middleLineCount)break;',
        '        float fi=float(i); float t=fi/max(float(middleLineCount-1),1.0);',
        '        float a=middleWavePosition.z*log(length(baseUv)+1.0); vec2 rv=baseUv*rot(a);',
        '        col+=getLineColor(t,b)*wave(rv+vec2(middleLineDistance*fi+middleWavePosition.x,middleWavePosition.y),2.0+0.15*fi,baseUv,mouseUv,interactive);}}',
        '    if(enableTop){for(int i=0;i<12;++i){if(i>=topLineCount)break;',
        '        float fi=float(i); float t=fi/max(float(topLineCount-1),1.0);',
        '        float a=topWavePosition.z*log(length(baseUv)+1.0); vec2 rv=baseUv*rot(a); rv.x*=-1.0;',
        '        col+=getLineColor(t,b)*wave(rv+vec2(topLineDistance*fi+topWavePosition.x,topWavePosition.y),1.0+0.2*fi,baseUv,mouseUv,interactive)*0.1;}}',
        '    fragColor=vec4(col,1.0);',
        '}',
        'void main(){vec4 c=vec4(0.0);mainImage(c,gl_FragCoord.xy);outColor=c;}'
    ].join('\n');

    var canvas = document.getElementById('bg-canvas');
    var gl = canvas.getContext('webgl2');
    if (!gl) return;

    function mkShader(t,s){var sh=gl.createShader(t);gl.shaderSource(sh,s);gl.compileShader(sh);return sh;}
    var prog=gl.createProgram();
    gl.attachShader(prog,mkShader(gl.VERTEX_SHADER,VS));
    gl.attachShader(prog,mkShader(gl.FRAGMENT_SHADER,FS));
    gl.linkProgram(prog); gl.useProgram(prog);

    var vb=gl.createBuffer();
    gl.bindBuffer(gl.ARRAY_BUFFER,vb);
    gl.bufferData(gl.ARRAY_BUFFER,new Float32Array([-1,-1,1,-1,-1,1,1,1]),gl.STATIC_DRAW);
    var ap=gl.getAttribLocation(prog,'a_pos');
    gl.enableVertexAttribArray(ap);
    gl.vertexAttribPointer(ap,2,gl.FLOAT,false,0,0);

    function ul(n){return gl.getUniformLocation(prog,n);}
    var U={iTime:ul('iTime'),iResolution:ul('iResolution'),animationSpeed:ul('animationSpeed'),
        enableTop:ul('enableTop'),enableMiddle:ul('enableMiddle'),enableBottom:ul('enableBottom'),
        topLineCount:ul('topLineCount'),middleLineCount:ul('middleLineCount'),bottomLineCount:ul('bottomLineCount'),
        topLineDistance:ul('topLineDistance'),middleLineDistance:ul('middleLineDistance'),bottomLineDistance:ul('bottomLineDistance'),
        topWavePosition:ul('topWavePosition'),middleWavePosition:ul('middleWavePosition'),bottomWavePosition:ul('bottomWavePosition'),
        iMouse:ul('iMouse'),interactive:ul('interactive'),bendRadius:ul('bendRadius'),bendStrength:ul('bendStrength'),bendInfluence:ul('bendInfluence'),
        parallax:ul('parallax'),parallaxStrength:ul('parallaxStrength'),parallaxOffset:ul('parallaxOffset'),
        lineGradient:ul('lineGradient[0]'),lineGradientCount:ul('lineGradientCount')};

    gl.uniform1f(U.animationSpeed,1.0);
    gl.uniform1i(U.enableTop,1);gl.uniform1i(U.enableMiddle,1);gl.uniform1i(U.enableBottom,1);
    gl.uniform1i(U.topLineCount,6);gl.uniform1i(U.middleLineCount,6);gl.uniform1i(U.bottomLineCount,6);
    gl.uniform1f(U.topLineDistance,0.05);gl.uniform1f(U.middleLineDistance,0.05);gl.uniform1f(U.bottomLineDistance,0.05);
    gl.uniform3f(U.topWavePosition,10.0,0.5,-0.4);
    gl.uniform3f(U.middleWavePosition,5.0,0.0,0.2);
    gl.uniform3f(U.bottomWavePosition,2.0,-0.7,-1.0);
    gl.uniform1i(U.interactive,1);gl.uniform1f(U.bendRadius,1.0);gl.uniform1f(U.bendStrength,-0.7);
    gl.uniform1i(U.parallax,1);gl.uniform1f(U.parallaxStrength,0.2);
    gl.uniform3fv(U.lineGradient,new Float32Array([233/255,69/255,245/255,47/255,75/255,192/255,233/255,69/255,245/255,1,1,1,1,1,1,1,1,1,1,1,1,1,1,1]));
    gl.uniform1i(U.lineGradientCount,3);

    var DAMP=0.03,mouse={tx:0,ty:0,cx:0,cy:0},infl={t:0,c:0},prlx={tx:0,ty:0,cx:0,cy:0};
    document.addEventListener('pointermove',function(e){
        var dpr=window.devicePixelRatio||1;
        mouse.tx=e.clientX*dpr; mouse.ty=(window.innerHeight-e.clientY)*dpr; infl.t=1.0;
        prlx.tx=(e.clientX-window.innerWidth/2)/window.innerWidth*0.2;
        prlx.ty=-(e.clientY-window.innerHeight/2)/window.innerHeight*0.2;
    });
    document.addEventListener('pointerleave',function(){infl.t=0;});
    function lerp(a,b,t){return a+(b-a)*t;}

    var w0=0,h0=0,start=null;
    function frame(ts){
        if(!start)start=ts; var t=(ts-start)*0.001;
        var dpr=window.devicePixelRatio||1,w=window.innerWidth,h=window.innerHeight;
        var cw=Math.round(w*dpr),ch=Math.round(h*dpr);
        if(cw!==w0||ch!==h0){canvas.width=cw;canvas.height=ch;canvas.style.width=w+'px';canvas.style.height=h+'px';gl.viewport(0,0,cw,ch);w0=cw;h0=ch;}
        mouse.cx=lerp(mouse.cx,mouse.tx,DAMP);mouse.cy=lerp(mouse.cy,mouse.ty,DAMP);
        infl.c=lerp(infl.c,infl.t,DAMP);prlx.cx=lerp(prlx.cx,prlx.tx,DAMP);prlx.cy=lerp(prlx.cy,prlx.ty,DAMP);
        gl.uniform1f(U.iTime,t);gl.uniform3f(U.iResolution,cw,ch,1.0);
        gl.uniform2f(U.iMouse,mouse.cx,mouse.cy);gl.uniform1f(U.bendInfluence,infl.c);
        gl.uniform2f(U.parallaxOffset,prlx.cx,prlx.cy);
        gl.drawArrays(gl.TRIANGLE_STRIP,0,4);
        requestAnimationFrame(frame);
    }
    requestAnimationFrame(frame);

    window.togglePw=function(id,iconId){
        var inp=document.getElementById(id),icon=document.getElementById(iconId);
        if(inp.type==='password'){
            inp.type='text';
            icon.innerHTML='<path d="M9.88 9.88a3 3 0 1 0 4.24 4.24"/><path d="M10.73 5.08A10.43 10.43 0 0 1 12 5c7 0 10 7 10 7a13.16 13.16 0 0 1-1.67 2.68"/><path d="M6.61 6.61A13.526 13.526 0 0 0 2 12s3 7 10 7a9.74 9.74 0 0 0 5.39-1.61"/><line x1="2" x2="22" y1="2" y2="22"/>';
        }else{
            inp.type='password';
            icon.innerHTML='<path d="M2 12s3-7 10-7 10 7 10 7-3 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>';
        }
    };
}());
</script>
</body>
</html>
