import { spawn } from 'node:child_process'
import { setTimeout as delay } from 'node:timers/promises'
import { fileURLToPath } from 'node:url'

const root = fileURLToPath(new URL('../', import.meta.url))
const backendDirectory = fileURLToPath(new URL('../backend/', import.meta.url))
const frontendDirectory = fileURLToPath(new URL('../frontend/', import.meta.url))
const apiUrl = process.env.LARAVEL_API_URL || 'http://127.0.0.1:8000'
const startsLocalBackend = !process.env.LARAVEL_API_URL
let backend
let frontend
let shuttingDown = false

function stop(exitCode = 0) {
  if (shuttingDown) return
  shuttingDown = true
  process.exitCode = exitCode
  backend?.kill('SIGTERM')
  frontend?.kill('SIGTERM')
}

for (const signal of ['SIGINT', 'SIGTERM']) {
  process.on(signal, () => stop(0))
}

function watchProcess(child, name) {
  child.on('error', (error) => {
    console.error(`Could not start ${name}: ${error.message}`)
    stop(1)
  })
  child.on('exit', (code, signal) => {
    if (shuttingDown) return
    if (signal) {
      console.error(`${name} stopped after ${signal}`)
    } else if (code !== 0) {
      console.error(`${name} exited with code ${code}`)
    }
    stop(code ?? 1)
  })
}

async function waitForBackend() {
  const healthUrl = new URL('/up', apiUrl)
  const deadline = Date.now() + 15000

  while (Date.now() < deadline && !shuttingDown) {
    if (backend.exitCode !== null) {
      throw new Error('Laravel exited before its API became ready.')
    }
    try {
      const response = await fetch(healthUrl, { signal: AbortSignal.timeout(1000) })
      if (response.ok) return
    } catch {
      // Laravel may need a moment to initialize before its health route responds.
    }
    await delay(250)
  }

  if (!shuttingDown) {
    throw new Error(`Laravel did not respond at ${healthUrl} within 15 seconds.`)
  }
}

try {
  if (startsLocalBackend) {
    backend = spawn(
      'php',
      ['artisan', 'serve', '--host', '127.0.0.1', '--port', '8000'],
      { cwd: backendDirectory, stdio: 'inherit' },
    )
    watchProcess(backend, 'Laravel API')
    await waitForBackend()
  } else {
    console.log(`Using configured Laravel API at ${apiUrl}`)
  }

  if (!shuttingDown) {
    console.log('Starting Vue development server…')
    frontend = spawn(
      process.execPath,
      ['node_modules/vite/bin/vite.js', ...process.argv.slice(2)],
      { cwd: frontendDirectory, stdio: 'inherit' },
    )
    watchProcess(frontend, 'Vite')
  }
} catch (error) {
  console.error(error instanceof Error ? error.message : error)
  stop(1)
}
