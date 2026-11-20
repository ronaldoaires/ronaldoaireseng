import * as THREE from 'three';
import { GLTFLoader } from 'three/addons/loaders/GLTFLoader.js';
import { OrbitControls } from 'three/addons/controls/OrbitControls.js';

const materials = {
    IfcFooting: new THREE.MeshStandardMaterial({ color: '#253e72', roughness: 0.9, side: THREE.DoubleSide }),
    IfcColumn: new THREE.MeshStandardMaterial({ color: '#aabac8', roughness: 0.94, side: THREE.DoubleSide }),
    IfcBeam: new THREE.MeshStandardMaterial({ color: '#376f9b', roughness: 0.87, side: THREE.DoubleSide }),
    IfcSlab: new THREE.MeshStandardMaterial({ color: '#bfd1dc', roughness: 0.92, transparent: true, opacity: 0.82, side: THREE.DoubleSide }),
    IfcStairFlight: new THREE.MeshStandardMaterial({ color: '#d7e1e7', roughness: 0.92, side: THREE.DoubleSide }),
};

export async function mountStructureHero(root) {
    const stage = root.querySelector('[data-structure-stage]');
    const poster = root.querySelector('[data-structure-poster]');
    const controlsPanel = root.querySelector('[data-structure-controls]');

    if (! stage || ! poster || ! controlsPanel || ! root.dataset.structureModel || ! window.WebGL2RenderingContext) {
        return;
    }

    let model;
    let renderer;

    try {
        model = (await new GLTFLoader().loadAsync(root.dataset.structureModel)).scene;
        renderer = new THREE.WebGLRenderer({ alpha: true, antialias: true, powerPreference: 'low-power' });
    } catch {
        return;
    }

    model.traverse((object) => {
        if (object.isMesh) {
            object.material = materials[object.name] ?? materials[object.parent?.name] ?? materials.IfcColumn;
        }
    });

    const bounds = new THREE.Box3().setFromObject(model);
    const center = bounds.getCenter(new THREE.Vector3());
    const size = bounds.getSize(new THREE.Vector3());
    model.position.set(-center.x, -bounds.min.y, -center.z);
    model.updateMatrixWorld(true);

    const modelPoints = [];
    model.traverse((object) => {
        if (! object.isMesh) {
            return;
        }

        const positions = object.geometry.attributes.position;
        for (let index = 0; index < positions.count; index++) {
            modelPoints.push(new THREE.Vector3().fromBufferAttribute(positions, index).applyMatrix4(object.matrixWorld));
        }
    });

    const scene = new THREE.Scene();
    scene.add(model);
    scene.add(new THREE.AmbientLight('#ffffff', 2.2));
    const sunlight = new THREE.DirectionalLight('#ffffff', 2.8);
    sunlight.position.set(-8, 16, 12);
    scene.add(sunlight);
    const fillLight = new THREE.DirectionalLight('#93b8fd', 1.1);
    fillLight.position.set(10, 7, -9);
    scene.add(fillLight);

    renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 1.5));
    renderer.outputColorSpace = THREE.SRGBColorSpace;
    renderer.domElement.className = 'absolute inset-0 h-full w-full cursor-grab active:cursor-grabbing';
    renderer.domElement.setAttribute('role', 'img');
    renderer.domElement.setAttribute('aria-label', 'Modelo 3D de um projeto estrutural real. Arraste para girar, use a roda do mouse ou dois dedos para aproximar e afastar.');
    renderer.domElement.tabIndex = 0;

    const camera = new THREE.PerspectiveCamera(35, 1, 0.1, 200);
    const target = new THREE.Vector3(0, size.y * 0.43, 0);
    const direction = new THREE.Vector3(0.9, 0.75, 1).normalize();
    const viewDirections = {
        perspective: direction,
        front: new THREE.Vector3(0, 0.12, 1).normalize(),
        side: new THREE.Vector3(1, 0.12, 0).normalize(),
        top: new THREE.Vector3(0.01, 1, 0.01).normalize(),
    };
    const orbit = new OrbitControls(camera, renderer.domElement);
    orbit.target.copy(target);
    orbit.enablePan = false;
    orbit.enableZoom = true;
    orbit.minPolarAngle = 0.01;
    orbit.maxPolarAngle = Math.PI / 2.05;

    const fitCamera = (viewDirection) => {
        let distance = size.length() * 0.7;
        const projected = new THREE.Vector3();
        for (let attempt = 0; attempt < 40; attempt++) {
            camera.position.copy(target).addScaledVector(viewDirection, distance);
            camera.lookAt(target);
            camera.updateMatrixWorld();

            if (modelPoints.every((point) => {
                projected.copy(point).project(camera);
                return Math.abs(projected.x) < 0.96 && Math.abs(projected.y) < 0.96 && projected.z < 1;
            })) {
                break;
            }

            distance *= 1.08;
        }

        orbit.minDistance = distance * 0.6;
        orbit.maxDistance = distance * 1.8;
        orbit.target.copy(target);
        orbit.update();
    };

    let hasFittedCamera = false;
    const render = () => renderer.render(scene, camera);
    const resize = () => {
        const { width, height } = stage.getBoundingClientRect();
        if (width < 1 || height < 1) {
            return;
        }

        camera.aspect = width / height;
        camera.updateProjectionMatrix();
        renderer.setSize(width, height);
        const viewDirection = hasFittedCamera ? camera.position.clone().sub(target).normalize() : direction;
        fitCamera(viewDirection);
        hasFittedCamera = true;
        render();
    };

    stage.appendChild(renderer.domElement);
    resize();
    poster.classList.add('hidden');
    controlsPanel.hidden = false;

    orbit.addEventListener('change', render);
    new ResizeObserver(resize).observe(stage);

    const viewSelect = root.querySelector('[data-structure-view]');
    const rotateButton = root.querySelector('[data-structure-rotate]');
    const fullscreenButton = root.querySelector('[data-structure-fullscreen]');
    let rotationFrame = null;
    let previousFrameTime = null;

    const animateRotation = (time) => {
        if (! orbit.autoRotate || document.hidden || ! root.isConnected) {
            rotationFrame = null;
            return;
        }

        const deltaTime = previousFrameTime === null ? 0 : Math.min((time - previousFrameTime) / 1000, 0.1);
        previousFrameTime = time;
        orbit.update(deltaTime);
        rotationFrame = requestAnimationFrame(animateRotation);
    };

    const stopRotation = () => {
        orbit.autoRotate = false;
        rotateButton.setAttribute('aria-pressed', 'false');
        rotateButton.setAttribute('aria-label', 'Iniciar rotação automática');
        rotateButton.textContent = 'Girar';
        if (rotationFrame !== null) {
            cancelAnimationFrame(rotationFrame);
            rotationFrame = null;
        }
        previousFrameTime = null;
    };

    const zoom = (factor) => {
        const distance = camera.position.distanceTo(target);
        const nextDistance = THREE.MathUtils.clamp(distance * factor, orbit.minDistance, orbit.maxDistance);
        camera.position.sub(target).multiplyScalar(nextDistance / distance).add(target);
        orbit.update();
    };

    root.querySelector('[data-structure-zoom-in]')?.addEventListener('click', () => zoom(0.9));
    root.querySelector('[data-structure-zoom-out]')?.addEventListener('click', () => zoom(1.1));
    viewSelect?.addEventListener('change', () => {
        stopRotation();
        fitCamera(viewDirections[viewSelect.value] ?? direction);
        render();
    });
    rotateButton?.addEventListener('click', () => {
        if (orbit.autoRotate) {
            stopRotation();
            return;
        }

        orbit.autoRotate = true;
        orbit.autoRotateSpeed = 0.6;
        rotateButton.setAttribute('aria-pressed', 'true');
        rotateButton.setAttribute('aria-label', 'Pausar rotação automática');
        rotateButton.textContent = 'Pausar';
        previousFrameTime = null;
        rotationFrame = requestAnimationFrame(animateRotation);
    });
    document.addEventListener('visibilitychange', () => {
        if (! document.hidden && orbit.autoRotate && rotationFrame === null) {
            previousFrameTime = null;
            rotationFrame = requestAnimationFrame(animateRotation);
        }
    });

    const updateFullscreen = () => {
        const isExpanded = document.fullscreenElement === root || root.classList.contains('structure-hero-expanded');
        fullscreenButton.textContent = isExpanded ? 'Sair da tela cheia' : 'Tela cheia';
        fullscreenButton.setAttribute('aria-pressed', String(isExpanded));
        requestAnimationFrame(resize);
    };

    const setExpandedFallback = (isExpanded) => {
        root.classList.toggle('structure-hero-expanded', isExpanded);
        document.documentElement.classList.toggle('structure-hero-expanded-open', isExpanded);
        updateFullscreen();
    };

    fullscreenButton.addEventListener('click', async () => {
        if (root.classList.contains('structure-hero-expanded')) {
            setExpandedFallback(false);
            return;
        }

        if (document.fullscreenElement === root) {
            await document.exitFullscreen();
            return;
        }

        if (document.fullscreenEnabled && root.requestFullscreen) {
            try {
                await root.requestFullscreen();
                return;
            } catch {
                setExpandedFallback(true);
                return;
            }
        }

        setExpandedFallback(true);
    });
    document.addEventListener('fullscreenchange', updateFullscreen);
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && root.classList.contains('structure-hero-expanded')) {
            setExpandedFallback(false);
        }
    });

    root.querySelector('[data-structure-reset]')?.addEventListener('click', () => {
        stopRotation();
        viewSelect.value = 'perspective';
        fitCamera(direction);
        render();
    });

    renderer.domElement.addEventListener('keydown', (event) => {
        const directions = { ArrowLeft: -1, ArrowRight: 1 };
        if (event.key in directions) {
            event.preventDefault();
            camera.position.sub(target).applyAxisAngle(new THREE.Vector3(0, 1, 0), directions[event.key] * 0.18).add(target);
            orbit.update();
        }
    });

    renderer.domElement.addEventListener('webglcontextlost', (event) => {
        event.preventDefault();
        stopRotation();
        if (root.classList.contains('structure-hero-expanded')) {
            setExpandedFallback(false);
        }
        renderer.domElement.remove();
        poster.classList.remove('hidden');
        controlsPanel.hidden = true;
    });
}
