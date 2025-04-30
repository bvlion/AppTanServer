import { onRequest } from "firebase-functions/v2/https"
import * as logger from "firebase-functions/logger"
import gplay from 'google-play-scraper'

const validToken = process.env.BEARER_TOKEN

export const scrape = onRequest(async (request, response) => {
  const authHeader = request.headers['authorization'] || ''
  const token = authHeader.replace(/^Bearer\s+/i, '')

  if (token !== validToken) {
    logger.error('Unauthorized access attempt', { validToken })
    return response.status(401).json({ error: 'Unauthorized' })
  }

  const packageName = request.query.packageName || request.body?.packageName
  if (!packageName) {
    return response.status(400).json({ error: 'Missing Parameter' })
  }

  try {
    const appData = await gplay.app({
      appId: packageName,
      lang: 'ja',
      country: 'jp',
    })

    response.json({
      title: appData.title,
      description: appData.description,
    })
  } catch (error) {
    response.status(500).json({
      error: 'Failed to fetch from Google Play',
      detail: error.message,
    })
  }
})
