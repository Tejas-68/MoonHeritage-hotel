package com.moonheritage.config;

import org.springframework.context.annotation.Configuration;
import org.springframework.data.mongodb.config.EnableMongoAuditing;

/**
 * Enables @CreatedDate and @LastModifiedDate on MongoDB documents.
 */
@Configuration
@EnableMongoAuditing
public class MongoConfig {
}
