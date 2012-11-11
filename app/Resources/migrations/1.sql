UPDATE banner b join campaign c ON c.id=b.campaign_id SET b.advertiser_id = c.advertiser_id;
