import { describe, it, mock } from 'node:test'
import assert from 'node:assert/strict'
import { scrapeHandler } from '../index.mjs'

const createResponseMock = () => {
  const res = {
    statusCode: 200,
    body: null,
    status(code) {
      this.statusCode = code
      return this
    },
    json(payload) {
      this.body = payload
      return this
    }
  }
  return res
}

describe('scrapeHandler', () => {
  it('returns 400 when packageName is missing', async () => {
    const res = createResponseMock()
    await scrapeHandler({ query: {}, body: {} }, res, { app: mock.fn() })

    assert.equal(res.statusCode, 400)
    assert.deepEqual(res.body, { error: 'Missing Parameter' })
  })

  it('returns app data when fetch succeeds', async () => {
    const client = {
      app: mock.fn(() => Promise.resolve({
        title: 'Example App',
        description: 'Sample description'
      }))
    }
    const res = createResponseMock()

    await scrapeHandler({ query: { packageName: 'com.example' }, body: {} }, res, client)

    assert.equal(res.statusCode, 200)
    assert.deepEqual(res.body, {
      title: 'Example App',
      description: 'Sample description'
    })
  })

  it('returns 500 when fetch throws', async () => {
    const client = {
      app: mock.fn(() => Promise.reject(new Error('boom')))
    }
    const res = createResponseMock()

    await scrapeHandler({ query: { packageName: 'com.example' }, body: {} }, res, client)

    assert.equal(res.statusCode, 500)
    assert.deepEqual(res.body, {
      error: 'Failed to fetch from Google Play',
      detail: 'boom'
    })
  })
})
