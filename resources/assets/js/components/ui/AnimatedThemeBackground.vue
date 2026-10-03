<template>
  <div
    v-if="isAnimatedTheme"
    class="animated-theme-bg fixed inset-0 pointer-events-none overflow-hidden select-none z-0"
    aria-hidden="true"
  >
    <canvas ref="canvasRef" class="w-full h-full block" />
  </div>
</template>

<script lang="ts" setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { themeStore } from '@/stores/themeStore'

interface Particle {
  x: number
  y: number
  radius: number
  color: string
  alpha: number
  speedX: number
  speedY: number
  rotation: number
  rotationSpeed: number
  oscillationSpeed: number
  oscillationDistance: number
  baseX: number
  type?: 'petal' | 'sparkle' | 'lantern' | 'ember' | 'firework'
  life?: number
  maxLife?: number
}

const canvasRef = ref<HTMLCanvasElement | null>(null)
let animationFrameId: number | null = null
let particles: Particle[] = []
let width = 0
let height = 0

const currentTheme = computed(() => themeStore.getCurrentTheme())
const activeThemeId = computed(() => currentTheme.value?.id)

const animatedThemeIds = ['khmer-new-year', 'pchum-ben', 'water-festival', 'angkor-sunrise']
const isAnimatedTheme = computed(() => animatedThemeIds.includes(activeThemeId.value || ''))

const setupCanvas = () => {
  const canvas = canvasRef.value
  if (!canvas) {
    return
  }

  const pixelRatio = Math.min(window.devicePixelRatio || 1, 2)
  width = window.innerWidth
  height = window.innerHeight

  canvas.width = width * pixelRatio
  canvas.height = height * pixelRatio

  const context = canvas.getContext('2d')
  if (context) {
    context.scale(pixelRatio, pixelRatio)
  }

  initParticles()
}

const initParticles = () => {
  particles = []
  const themeId = activeThemeId.value

  if (themeId === 'khmer-new-year') {
    // Khmer New Year: Floating 5-point star lanterns, white/pink jasmine petals, and golden sparkles
    const particleCount = 42
    for (let index = 0; index < particleCount; index += 1) {
      const isStarLantern = index < 6
      const isPetal = !isStarLantern && index % 2 === 0
      const initialX = Math.random() * width
      particles.push({
        x: initialX,
        y: Math.random() * height,
        baseX: initialX,
        radius: isStarLantern ? 14 + Math.random() * 8 : isPetal ? 6 + Math.random() * 6 : 2 + Math.random() * 3,
        color: isStarLantern ? '#facc15' : isPetal ? '#ffffff' : Math.random() > 0.4 ? '#fbbf24' : '#38bdf8',
        alpha: isStarLantern ? 0.75 + Math.random() * 0.2 : 0.35 + Math.random() * 0.5,
        speedX: -0.5 + Math.random() * 1.0,
        speedY: isStarLantern ? 0.3 + Math.random() * 0.5 : 0.6 + Math.random() * 1.1,
        rotation: Math.random() * Math.PI * 2,
        rotationSpeed: -0.015 + Math.random() * 0.03,
        oscillationSpeed: 0.012 + Math.random() * 0.02,
        oscillationDistance: isStarLantern ? 25 + Math.random() * 30 : 15 + Math.random() * 25,
        type: isStarLantern ? 'lantern' : isPetal ? 'petal' : 'sparkle',
      })
    }
  } else if (themeId === 'pchum-ben') {
    // Pchum Ben: Serene floating lotus blossoms, tranquil water ripples, and golden candle embers
    const particleCount = 44
    for (let index = 0; index < particleCount; index += 1) {
      const isLotus = index < 8
      const isRipple = !isLotus && index < 14
      const initialX = Math.random() * width
      particles.push({
        x: initialX,
        y: isLotus || isRipple ? height * 0.65 + Math.random() * (height * 0.3) : Math.random() * height,
        baseX: initialX,
        radius: isLotus ? 14 + Math.random() * 10 : isRipple ? 20 + Math.random() * 40 : 2 + Math.random() * 3,
        color: isLotus ? '#f472b6' : isRipple ? '#5eead4' : Math.random() > 0.4 ? '#f59e0b' : '#34d399',
        alpha: isLotus ? 0.7 + Math.random() * 0.25 : isRipple ? 0.25 + Math.random() * 0.2 : 0.3 + Math.random() * 0.5,
        speedX: (-0.3 + Math.random() * 0.6) * 0.4,
        speedY: isLotus ? (-0.1 + Math.random() * 0.2) * 0.2 : isRipple ? 0 : -(0.3 + Math.random() * 0.6),
        rotation: Math.random() * Math.PI * 2,
        rotationSpeed: -0.008 + Math.random() * 0.016,
        oscillationSpeed: 0.01 + Math.random() * 0.02,
        oscillationDistance: isLotus ? 15 + Math.random() * 20 : 8 + Math.random() * 15,
        type: isLotus ? 'lantern' : isRipple ? 'firework' : 'ember',
      })
    }
  } else if (themeId === 'water-festival') {
    // Water Festival: Dynamic river ripples, racing boat spray, and celebratory night sky fireworks
    const particleCount = 48
    for (let index = 0; index < particleCount; index += 1) {
      const isSpray = index % 3 === 0
      const isWaterShimmer = !isSpray && index % 2 === 0
      const initialX = Math.random() * width
      particles.push({
        x: initialX,
        y: isWaterShimmer || isSpray ? height * 0.62 + Math.random() * (height * 0.35) : Math.random() * (height * 0.6),
        baseX: initialX,
        radius: isWaterShimmer ? 22 + Math.random() * 35 : isSpray ? 2.5 + Math.random() * 3 : 2 + Math.random() * 3,
        color: isWaterShimmer
          ? Math.random() > 0.5
            ? '#38bdf8'
            : '#06b6d4'
          : isSpray
            ? '#ffffff'
            : Math.random() > 0.5
              ? '#facc15'
              : '#ef4444',
        alpha: isSpray ? 0.6 + Math.random() * 0.4 : 0.25 + Math.random() * 0.45,
        speedX: isSpray ? 1.0 + Math.random() * 1.5 : 0.4 + Math.random() * 0.8,
        speedY: isSpray ? -0.4 + Math.random() * 0.8 : isWaterShimmer ? 0 : -0.2 + Math.random() * 0.5,
        rotation: 0,
        rotationSpeed: 0,
        oscillationSpeed: 0.02 + Math.random() * 0.035,
        oscillationDistance: isSpray ? 10 + Math.random() * 15 : 25 + Math.random() * 40,
        type: isWaterShimmer ? 'lantern' : isSpray ? 'petal' : 'sparkle',
      })
    }
  } else if (themeId === 'angkor-sunrise') {
    // Celestial golden sunbeams and morning mist dust
    const particleCount = 35
    for (let index = 0; index < particleCount; index += 1) {
      const initialX = Math.random() * width
      particles.push({
        x: initialX,
        y: Math.random() * height,
        baseX: initialX,
        radius: 3 + Math.random() * 5,
        color: Math.random() > 0.5 ? '#fde047' : '#f59e0b',
        alpha: 0.2 + Math.random() * 0.4,
        speedX: -0.3 + Math.random() * 0.6,
        speedY: -(0.2 + Math.random() * 0.5),
        rotation: Math.random() * Math.PI,
        rotationSpeed: -0.01 + Math.random() * 0.02,
        oscillationSpeed: 0.01 + Math.random() * 0.015,
        oscillationDistance: 15 + Math.random() * 20,
        type: 'sparkle',
      })
    }
  }
}

let tick = 0

const render = () => {
  const canvas = canvasRef.value
  if (!canvas || !isAnimatedTheme.value) {
    return
  }

  const context = canvas.getContext('2d')
  if (!context) {
    return
  }

  context.clearRect(0, 0, width, height)
  tick += 1

  const themeId = activeThemeId.value

  for (const particle of particles) {
    particle.rotation += particle.rotationSpeed
    particle.y += particle.speedY
    particle.x = particle.baseX + Math.sin(tick * particle.oscillationSpeed) * particle.oscillationDistance

    // Screen wrapping
    if (particle.speedY > 0 && particle.y > height + 20) {
      particle.y = -20
      particle.x = Math.random() * width
      particle.baseX = particle.x
    } else if (particle.speedY < 0 && particle.y < -20) {
      particle.y = height + 20
      particle.x = Math.random() * width
      particle.baseX = particle.x
    }

    if (particle.x > width + 30) {
      particle.x = -30
      particle.baseX = -30
    } else if (particle.x < -30) {
      particle.x = width + 30
      particle.baseX = width + 30
    }

    context.save()
    context.translate(particle.x, particle.y)
    context.rotate(particle.rotation)
    context.globalAlpha = particle.alpha

    if (particle.type === 'petal') {
      // Draw stylized Khmer Rumduol / lotus blossom petal
      context.fillStyle = particle.color
      context.beginPath()
      context.ellipse(0, 0, particle.radius, particle.radius * 0.45, 0, 0, Math.PI * 2)
      context.fill()

      // Center vein
      context.strokeStyle = 'rgba(255, 255, 255, 0.4)'
      context.lineWidth = 1
      context.beginPath()
      context.moveTo(-particle.radius * 0.7, 0)
      context.lineTo(particle.radius * 0.7, 0)
      context.stroke()
    } else if (particle.type === 'lantern') {
      if (themeId === 'khmer-new-year') {
        // Draw miniature rotating 5-point Khmer star lantern (គោមផ្កាយ)
        const outerRadius = particle.radius
        const innerRadius = particle.radius * 0.42

        // Outer bamboo frame circle
        context.strokeStyle = 'rgba(254, 240, 138, 0.65)'
        context.lineWidth = 1.2
        context.beginPath()
        context.arc(0, 0, outerRadius * 0.85, 0, Math.PI * 2)
        context.stroke()

        // 5-Point star shape
        context.beginPath()
        for (let i = 0; i < 5; i += 1) {
          const outerAngle = (i * 2 * Math.PI) / 5 - Math.PI / 2
          const innerAngle = outerAngle + Math.PI / 5
          if (i === 0) {
            context.moveTo(Math.cos(outerAngle) * outerRadius, Math.sin(outerAngle) * outerRadius)
          } else {
            context.lineTo(Math.cos(outerAngle) * outerRadius, Math.sin(outerAngle) * outerRadius)
          }
          context.lineTo(Math.cos(innerAngle) * innerRadius, Math.sin(innerAngle) * innerRadius)
        }
        context.closePath()
        context.fillStyle = 'rgba(250, 204, 21, 0.85)'
        context.fill()
        context.strokeStyle = '#b45309'
        context.lineWidth = 1
        context.stroke()

        // Glowing center candle
        context.fillStyle = '#ffffff'
        context.beginPath()
        context.arc(0, 0, innerRadius * 0.45, 0, Math.PI * 2)
        context.fill()
      } else if (themeId === 'pchum-ben') {
        // Draw glowing floating lotus lantern
        // Water glow
        context.fillStyle = 'rgba(245, 158, 11, 0.25)'
        context.beginPath()
        context.ellipse(0, particle.radius * 0.4, particle.radius * 1.5, particle.radius * 0.5, 0, 0, Math.PI * 2)
        context.fill()

        // Lotus base
        context.fillStyle = particle.color
        context.beginPath()
        context.arc(0, 0, particle.radius * 0.8, 0, Math.PI)
        context.fill()

        // Candle flame
        const flameFlicker = Math.sin(tick * 0.15 + particle.baseX) * 2
        context.fillStyle = '#fef08a'
        context.beginPath()
        context.ellipse(0, -particle.radius * 0.3 + flameFlicker * 0.3, 3, 6 + flameFlicker, 0, 0, Math.PI * 2)
        context.fill()
      } else {
        // Water ripple shimmer ellipse for Water Festival
        context.fillStyle = particle.color
        context.beginPath()
        context.ellipse(0, 0, particle.radius, 2.5, 0, 0, Math.PI * 2)
        context.fill()
      }
    } else if (particle.type === 'firework') {
      // Expanding water ripple ring on calm pond
      const rippleSize = ((tick * 0.4 + particle.baseX) % 60) + particle.radius
      const rippleFade = Math.max(0, 1 - rippleSize / (60 + particle.radius))
      context.globalAlpha = particle.alpha * rippleFade
      context.strokeStyle = particle.color
      context.lineWidth = 1.2
      context.beginPath()
      context.ellipse(0, 0, rippleSize, rippleSize * 0.35, 0, 0, Math.PI * 2)
      context.stroke()
    } else if (particle.type === 'ember') {
      // Glowing spiritual ember
      const flicker = Math.sin(tick * 0.1 + particle.baseX) * 0.2
      context.globalAlpha = Math.max(0.1, Math.min(1, particle.alpha + flicker))
      context.fillStyle = particle.color
      context.beginPath()
      context.arc(0, 0, particle.radius, 0, Math.PI * 2)
      context.fill()
    } else {
      // Sparkle / light orb
      const pulse = Math.sin(tick * 0.08 + particle.baseX) * 0.3
      context.globalAlpha = Math.max(0.1, Math.min(1, particle.alpha + pulse))
      context.fillStyle = particle.color
      context.beginPath()
      context.arc(0, 0, particle.radius, 0, Math.PI * 2)
      context.fill()

      // Cross gleam for larger sparkles
      if (particle.radius > 3) {
        context.strokeStyle = 'rgba(255, 255, 255, 0.6)'
        context.lineWidth = 1
        context.beginPath()
        context.moveTo(-particle.radius * 1.6, 0)
        context.lineTo(particle.radius * 1.6, 0)
        context.moveTo(0, -particle.radius * 1.6)
        context.lineTo(0, particle.radius * 1.6)
        context.stroke()
      }
    }

    context.restore()
  }

  animationFrameId = requestAnimationFrame(render)
}

const onResize = () => {
  setupCanvas()
}

const onVisibilityChange = () => {
  if (document.hidden) {
    if (animationFrameId) {
      cancelAnimationFrame(animationFrameId)
      animationFrameId = null
    }
  } else if (!animationFrameId && isAnimatedTheme.value) {
    animationFrameId = requestAnimationFrame(render)
  }
}

watch(isAnimatedTheme, enabled => {
  if (enabled) {
    setTimeout(() => {
      setupCanvas()
      if (!animationFrameId) {
        animationFrameId = requestAnimationFrame(render)
      }
    }, 50)
  } else if (animationFrameId) {
    cancelAnimationFrame(animationFrameId)
    animationFrameId = null
  }
})

watch(activeThemeId, () => {
  if (isAnimatedTheme.value) {
    initParticles()
  }
})

onMounted(() => {
  if (isAnimatedTheme.value) {
    setupCanvas()
    animationFrameId = requestAnimationFrame(render)
  }

  window.addEventListener('resize', onResize, { passive: true })
  document.addEventListener('visibilitychange', onVisibilityChange)
})

onBeforeUnmount(() => {
  if (animationFrameId) {
    cancelAnimationFrame(animationFrameId)
  }
  window.removeEventListener('resize', onResize)
  document.removeEventListener('visibilitychange', onVisibilityChange)
})
</script>

<style scoped>
.animated-theme-bg {
  transform: translateZ(0);
  will-change: transform;
}
</style>
