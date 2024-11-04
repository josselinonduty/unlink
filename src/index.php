<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>unlink.</title>
    <link rel="stylesheet" href="/public/styles/main.css">
    <script src="/public/scripts/bulma.js" defer></script>

    <?php require_once 'components/favicon.php'; ?>

    <style>
        #background {
            width: 100%;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            z-index: -1;
        }
    </style>
</head>

<body>
    <div id="background"></div>

    <?php require_once 'components/navbar.php'; ?>

    <section class="hero is-primary is-transparent is-bold is-fullheight-with-navbar">
        <div class="hero-body">
            <div class="container has-text-centered">
                <h1 class="title is-size-1 has-text-weight-normal">
                    <span class="is-underlined">links</span>. <span class="has-text-weight-bold">short.</span> <span class="is-italic">fast.</span>
                </h1>

                <?php if (isset($_SESSION['email'])): ?>
                    <a href="/create" class="button is-light">Shorten my link</a>
                <?php else: ?>
                    <a href="/register" class="button is-light">Shorten my link</a>
                <?php endif; ?>
            </div>
        </div>
    </section>
</body>

</html>

<script type="importmap">
    {
    "imports": {
        "three": "https://cdn.jsdelivr.net/npm/three@0.170.0/build/three.module.min.js"
        }
    }
</script>
<script type="module">
    import * as THREE from 'three';

    // Three.js Wave Animation
    const scene = new THREE.Scene();
    const camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
    const renderer = new THREE.WebGLRenderer({
        antialias: true,
        alpha: true
    });
    renderer.setSize(window.innerWidth, window.innerHeight);
    renderer.setPixelRatio(window.devicePixelRatio);
    document.getElementById('background').appendChild(renderer.domElement);

    // Plane geometry
    const geometry = new THREE.PlaneGeometry(20, 20, 30, 30);


    const material = new THREE.MeshBasicMaterial({
        color: 0xbab9d4,
        wireframe: true
    });
    const plane = new THREE.Mesh(geometry, material);
    scene.background = new THREE.Color(0x2f2f4c);
    scene.add(plane);

    camera.position.z = 5;

    function animate() {
        requestAnimationFrame(animate);
        plane.rotation.x = Math.sin(Date.now() * 0.005) * 0.0005;
        plane.rotation.y += Math.sin(Date.now() * 0.005) * 0.0005;

        // Create wave effect
        const positionAttribute = geometry.attributes.position;
        for (let i = 0; i < positionAttribute.count; i++) {
            const x = positionAttribute.getX(i);
            const y = positionAttribute.getY(i);
            const waveX1 = 0.4 * Math.sin(x * 1.5 + Date.now() * 0.0005);
            const waveY1 = 0.4 * Math.sin(y * 1.5 + Date.now() * 0.0005);
            positionAttribute.setZ(i, waveX1 + waveY1);
        }
        positionAttribute.needsUpdate = true;

        renderer.render(scene, camera);
    }

    animate();

    // Handle window resize
    window.addEventListener('resize', () => {
        renderer.setSize(window.innerWidth, window.innerHeight);
        camera.aspect = window.innerWidth / window.innerHeight;
        camera.updateProjectionMatrix();
    });
</script>